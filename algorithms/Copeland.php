<?php
/*
	Part of the Condorcet PHP Class, with Copeland Methods and others !

	Version : 0.4

	By Julien Boudry - MIT LICENSE (Please read LICENSE.txt)
	https://github.com/julien-boudry/Condorcet_Copeland-PHP_Class 
*/


// Registering algorithm
Condorcet::add_algos('Copeland') ;


// Copeland is a Condorcet Algorithm | http://en.wikipedia.org/wiki/Copeland_method
class Condorcet_Copeland
{
	// Config
	protected $_Pairwise ;
	protected $_options_count ;
	protected $_options ;

	// Copeland
	protected $_Copeland_comparison ;
	protected $_Copeland_result ;


	public function __construct (array $config)
	{
		$this->_Pairwise = $config['_Pairwise'] ;
		$this->_options_count = $config['_options_count'] ;
		$this->_options = $config['_options'] ;
	}


/////////// PUBLIC ///////////


	// Get the Coepland ranking
	public function get_result ()
	{
		// Cache
		if ( $this->_Copeland_result !== null )
		{
			return $this->_Copeland_result ;
		}

			//////

		// Comparison calculation
		$this->Copeland_make_comparison() ;

		// Ranking calculation
		$this->Copeland_make_ranking() ;


		// Return
		return $this->_Copeland_result ;
	}


	// Get the Copeland ranking
	public function get_stats ()
	{
		$this->get_result();

			//////

		$explicit = array() ;

		foreach ($this->_Copeland_comparison as $candidate_key => $value)
		{
			$explicit[Condorcet::get_static_option_id($candidate_key, $this->_options)] = $value ;
		}

		return $explicit ;
	}

		// Get only the Copeland Winner(s)
		public function get_winner ()
		{
			// If there is not Cache
			if ( $this->_Copeland_result === null )
			{
				$this->get_result();
			}

			return $this->_Copeland_result[1] ;
		}

		// Get only the Copeland Loser(s)
		public function get_loser ()
		{
			// If there is not Cache
			if ( $this->_Copeland_result === null )
			{
				$this->get_result();
			}

			return $this->_Copeland_result[count($this->_Copeland_result)] ;
		}



/////////// COMPUTE ///////////


	//:: COPELAND ALGORITHM. :://

	protected function Copeland_make_comparison ()
	{
		foreach ($this->_Pairwise as $candidat_key => $candidate_data)
		{
			$this->_Copeland_comparison[$candidat_key]['win'] = 0 ;
			$this->_Copeland_comparison[$candidat_key]['null'] = 0 ;
			$this->_Copeland_comparison[$candidat_key]['lose'] = 0 ;
			$this->_Copeland_comparison[$candidat_key]['balance'] = 0 ;


			foreach ($candidate_data['win'] as $opponenent['key'] => $opponenent['lose']) 
			{
				if ( $opponenent['lose'] > $candidate_data['lose'][$opponenent['key']] )
				{
					$this->_Copeland_comparison[$candidat_key]['win']++ ;
					$this->_Copeland_comparison[$candidat_key]['balance']++ ;
				}
				elseif ( $opponenent['lose'] === $candidate_data['lose'][$opponenent['key']] )
				{
					$this->_Copeland_comparison[$candidat_key]['null']++ ;
				}
				else
				{
					$this->_Copeland_comparison[$candidat_key]['lose']++ ;
					$this->_Copeland_comparison[$candidat_key]['balance']-- ;
				}
			}
		}
	}

	protected function Copeland_make_ranking ()
	{
		$this->_Copeland_result = array() ;

		// Calculate ranking
		$challenge = array () ;
		$rank = 1 ;
		$done = 0 ;

		foreach ($this->_Copeland_comparison as $candidate_key => $candidate_data)
		{
			$challenge[$candidate_key] = $candidate_data['balance'] ;
		}

		while ($done < $this->_options_count)
		{
			$looking = max($challenge) ;

			foreach ($challenge as $candidate => $value)
			{
				if ($value === $looking)
				{
					$this->_Copeland_result[$rank][] = Condorcet::get_static_option_id($candidate, $this->_options) ;

					$done++ ; unset($challenge[$candidate]) ;
				}
			}


			$this->_Copeland_result[$rank] = implode(',', $this->_Copeland_result[$rank]) ;
			$rank++ ;
		}
	}

}