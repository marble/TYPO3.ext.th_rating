<?php
namespace Thucke\ThRating\Domain\Model;
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Thomas Hucke <thucke@web.de> 
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Model for object rating  
 *
 * @author		Thomas Hucke <thucke@web.de>
 * @copyright 	Copyright belongs to the respective authors
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope 		beta
 * @entity
 */
class Rating extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	//TODO check deleted referenced records
	
	/**
	 * @var \Thucke\ThRating\Domain\Model\Ratingobject
	 * @validate \Thucke\ThRating\Domain\Validator\RatingobjectValidator
	 * @validate NotEmpty
	 */
	protected $ratingobject;
	
	/**
	 * The ratings uid of this object
	 *
	 * @var int
	 * @validate NumberRange(minimum = 1)
	 * @validate NotEmpty
	 */
	protected $ratedobjectuid;
	
	/**
	 * The ratings of this object
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Thucke\ThRating\Domain\Model\Vote>
	 * @lazy
	 * @cascade remove
	 */
	protected $votes;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;
	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface	$objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @var \Thucke\ThRating\Domain\Repository\VoteRepository
	 */
	protected $voteRepository;
	/**
	 * @param \Thucke\ThRating\Domain\Repository\VoteRepository $voteRepository
	 * @return void
	 */
	public function injectVoteRepository(\Thucke\ThRating\Domain\Repository\VoteRepository $voteRepository) {
		$this->voteRepository = $voteRepository;
	}

	/**
	 * @var \Thucke\ThRating\Service\ExtensionHelperService
	 */
	protected $extensionHelperService;
	/**
	 * @param	\Thucke\ThRating\Service\ExtensionHelperService $extensionHelperService
	 * @return	void
	 */
	public function injectExtensionHelperService( \Thucke\ThRating\Service\ExtensionHelperService $extensionHelperService ) {
		$this->extensionHelperService = $extensionHelperService;
	}

	/**
	 * The current calculated rates
	 *
	 * Redundant information to enhance performance in displaying calculated informations
	 * This is a JSON encoded string with the following keys
	 *	- votecounts(1...n)	vote counts of the specific ratingstep
	 * It be updated everytime a vote is created, changed or deleted.
	 * Specific handling must be defined when ratingsteps are added or removed or stepweights are changed
	 * Caclution of ratings:
	 *	currentrate = (  sum of all ( stepweight(n) * votecounts(n) ) ) / number of all votes
	 *	currentwidth = round (currentrate * 100 / number of ratingsteps, 1)
	 *
	 * @var string
	 */
	protected $currentrates;
	
	/**
	 * @var array
	 */
	protected $settings;
	
	/**
	 * Constructs a new rating object
	 * @return void
	 */
	public function __construct( \Thucke\ThRating\Domain\Model\Ratingobject $ratingobject = NULL, $ratedobjectuid=NULL ) {
		if ($ratingobject) $this->setRatingobject( $ratingobject );
		if ($ratedobjectuid) $this->setRatedobjectuid( $ratedobjectuid );
		$this->initializeObject();
	}


	/**
	 * Initializes a new rating object
	 * @return void
	 */
	public function initializeObject() {
		
		if ( empty($this->objectManager) ) {
			$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		}
		$this->settings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager')->getConfiguration('Settings', 'thRating', 'pi1');

		//Initialize vote storage if rating is new
		if (!is_object($this->votes)) {
			$this->votes = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		}
	}
	 
	/**
	 * Sets the ratingobject this rating is part of
	 *
	 * @param \Thucke\ThRating\Domain\Model\Ratingobject $ratingobject The Rating
	 * @return void
	 */
	public function setRatingobject(\Thucke\ThRating\Domain\Model\Ratingobject $ratingobject) {
		$this->ratingobject = $ratingobject;
		$this->setPid($ratingobject->getPid());
	}

	/**
	 * Returns the ratingobject this rating is part of
	 *
	 * @return	\Thucke\ThRating\Domain\Model\Ratingobject The ratingobject this rating is part of
	 */
	public function getRatingobject() {
		return $this->ratingobject;
	}

	/**
	 * Sets the rating object uid
	 * 
	 * @param int $ratedobjectuid
	 * @return void
	 */
	public function setRatedobjectuid($ratedobjectuid) {
		$this->ratedobjectuid = $ratedobjectuid;
	}
	
	/**
	 * Gets the rating object uid
	 * 
	 * @return int Rating object row uid field value
	 */
	public function getRatedobjectuid() {
		return $this->ratedobjectuid;
	}

	/**
	 * Adds a vote to this rating
	 *
	 * @param \Thucke\ThRating\Domain\Model\Vote $vote
	 * @return void
	 */
	public function addVote(\Thucke\ThRating\Domain\Model\Vote $vote) {
		$this->votes->attach($vote);
		$this->addCurrentrate($vote);
		$this->extensionHelperService->persistRepository('Thucke\ThRating\Domain\Repository\VoteRepository', $vote);
	}

	/**
	 * Updates an existing vote to this rating
	 *
	 * @param \Thucke\ThRating\Domain\Model\Vote $existingVote
	 * @param \Thucke\ThRating\Domain\Model\Vote $newVote
	 * @return void
	 */
	public function updateVote(\Thucke\ThRating\Domain\Model\Vote $existingVote, \Thucke\ThRating\Domain\Model\Vote $newVote) {
		$this->removeCurrentrate($existingVote);
		$existingVote->setVote($newVote->getVote());
		$this->addCurrentrate($existingVote);
		$this->extensionHelperService->persistRepository('Thucke\ThRating\Domain\Repository\VoteRepository', $existingVote);
	}

	/**
	 * Remove a vote from this rating
	 *
	 * @param \Thucke\ThRating\Domain\Model\Vote $voteToRemove The vote to be removed
	 * @return void
	 */
	public function removeVote(\Thucke\ThRating\Domain\Model\Vote $voteToRemove) {
		$this->removeCurrentrate($voteToRemove);
		$this->votes->detach($voteToRemove);
	}

	/**
	 * Remove all votes from this rating
	 *
	 * @return void
	 */
	public function removeAllVotes() {
		$this->votes = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		unset($this->currentrates);
	}

	/**
	 * Returns all votes in this rating
	 *
	 * @return	TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Thucke\ThRating\Domain\Model\Vote>
	 */
	public function getVotes() {
		return clone $this->votes;
	}

	/**
	 * Checks all votes of this rating and sets currentrates accordingly
	 *
	 * This method is used for maintenance to assure consistency
	 * @return void
	 */
	public function checkCurrentrates() {
		$currentratesDecoded['weightedVotes'] = array();
		$currentratesDecoded['sumWeightedVotes'] = array();
		foreach ( $this->getRatingobject()->getStepconfs() as $stepConf ) {
			$stepOrder = $stepConf->getSteporder();
			$voteCount = $this->voteRepository->countByMatchingRatingAndVote($this, $stepConf);
			$anonymousCount = $this->voteRepository->countAnonymousByMatchingRatingAndVote($this, $stepConf, $this->settings['mapAnonymous']);
			$currentratesDecoded['weightedVotes'][$stepOrder] = $voteCount * $stepConf->getStepweight();
			$currentratesDecoded['sumWeightedVotes'][$stepOrder] = $currentratesDecoded['weightedVotes'][$stepOrder] * $stepOrder;
			$numAllVotes += $voteCount;
			$numAllAnonymousVotes += $anonymousCount;
		}
		$currentratesDecoded['numAllVotes'] = $numAllVotes;
		$currentratesDecoded['anonymousVotes'] = $numAllAnonymousVotes;
		$this->currentrates = json_encode($currentratesDecoded);
	}


	/**
	 * Adds a vote to the calculations of this rating
	 *
	 * @param \Thucke\ThRating\Domain\Model\Vote $voting The vote to be added
	 * @return void
	 */
	public function addCurrentrate(\Thucke\ThRating\Domain\Model\Vote $voting) {
		if ( empty($this->currentrates) ) {
			$this->checkCurrentrates(); //initialize entry
		}
		$currentratesDecoded = json_decode($this->currentrates, true);
		$currentratesDecoded['numAllVotes']++;
		if ( $voting->isAnonymous() ) {
			$currentratesDecoded['anonymousVotes']++;
		}
		$votingStep = $voting->getVote();
		$votingSteporder = $votingStep->getSteporder(); 
		$votingStepweight = $votingStep->getStepweight(); 
		$currentratesDecoded['weightedVotes'][$votingSteporder] += $votingStepweight;
		$currentratesDecoded['sumWeightedVotes'][$votingSteporder] += $votingStepweight * $votingSteporder;
		$this->currentrates = json_encode($currentratesDecoded);
	}

	/**
	 * Adds a vote to the calculations of this rating
	 *
	 * @param \Thucke\ThRating\Domain\Model\Vote $voting The vote to be removed
	 * @return void
	 */
	public function removeCurrentrate(\Thucke\ThRating\Domain\Model\Vote $voting) {
		if ( empty($this->currentrates) ) {
			$this->checkCurrentrates(); //initialize entry
		}
		$currentratesDecoded = json_decode($this->currentrates, true);
		$currentratesDecoded['numAllVotes']--;
		if ( $voting->isAnonymous() ) {
			$currentratesDecoded['anonymousVotes']--;
		}
		$votingStep = $voting->getVote();
		$votingSteporder = $votingStep->getSteporder(); 
		$votingStepweight = $votingStep->getStepweight(); 
		$currentratesDecoded['weightedVotes'][$votingSteporder] -= $votingStepweight;
		$currentratesDecoded['sumWeightedVotes'][$votingSteporder] -= $votingStepweight * $votingSteporder;
		$this->currentrates = json_encode($currentratesDecoded);
	}

	/**
	 * Returns the calculated rating
	 *
	 * @return array
	 */
	public function getCurrentrates() {
		$currentratesDecoded = json_decode($this->currentrates, true);
		if (empty($currentratesDecoded['numAllVotes'])) {
			$this->checkCurrentrates();
			$currentratesDecoded = json_decode($this->currentrates, true);
		}
		$numAllVotes = $currentratesDecoded['numAllVotes'];
		if (!empty($numAllVotes)) {
			$currentrate = array_sum ( $currentratesDecoded['sumWeightedVotes'] ) / $numAllVotes;
		} else {
			$currentrate = 0;
			$numAllVotes = 0;
		}
		
		$sumAllWeightedVotes = array_sum($currentratesDecoded['weightedVotes']);

        //initialize array to handle missing stepconfs correctly
		$currentPollDimensions = array();		
		
		foreach ( $this->getRatingobject()->getStepconfs() as $stepConf ) {
			if ( empty($sumAllWeightedVotes) ) {
				//set current polling styles to zero percent and prevent division by zero error in lower formula
				$currentPollDimensions[$stepConf->getStepOrder()]['pctValue'] = 0;
			} else {
				//calculate current polling styles -> holds a percent value for usage in CSS to display polling relations
				$currentPollDimensions[$stepConf->getStepOrder()]['pctValue'] = round ( ($currentratesDecoded['weightedVotes'][$stepConf->getStepOrder()]  * 100) / $sumAllWeightedVotes, 1 );
			}
		}
		
		return array (	'currentrate' => $currentrate,
						'weightedVotes' => $currentratesDecoded['weightedVotes'],
						'sumWeightedVotes' => $currentratesDecoded['sumWeightedVotes'],
						'anonymousVotes' => $currentratesDecoded['anonymousVotes'],
						'currentPollDimensions' => $currentPollDimensions ,
						'numAllVotes' => $numAllVotes);
	}

	/**
	 * Returns the calculated rating in percent
	 *
	 * @return string
	 */
	public function getCalculatedRate() {
		$currentrate = $this->getCurrentrates();
		if (!empty($currentrate['weightedVotes'])) {
			$calculatedRate = round ( ($currentrate['currentrate']  * 100) / count($currentrate['weightedVotes']), 1 );
		} else {
			$calculatedRate = 0;
		}
		return $calculatedRate;
	}

}

?>