<?php

namespace App\Subscription;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionHelper{
  /** @var SubscriptionPlan[] */
  private $plans = [];
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	public function __construct(EntityManagerInterface $em) {
    $this->plans[] = new SubscriptionPlan(
      'plan_GbYpuXY2R0LbYf',
      'farmer_brent_monthly',
      '99'
    );

	  $this->plans[] = new SubscriptionPlan(
		  'plan_GbYrirrvJYKsV7',
		  'new_zeelander_monthly',
		  '199'
	  );

		$this->em = $em;
	}

  /**
   * @param $planId
   * @return SubscriptionPlan|null
   */
  public function findPlan($planId){
    foreach ($this->plans as $plan) {
      if ($plan->getPlanId() == $planId) {
        return $plan;
      }
    }
  }

  public function addSubscriptionToUser(
  	\Stripe\Subscription $stripeSubscription,
	  User $user
  ){
		$subscription = $user->getSubscription();
		if(!$subscription){
			$subscription = new Subscription();
			$subscription->setUser($user);
		}

		$subscription->activateSubscription(
			$stripeSubscription->plan->id,
			$stripeSubscription->id
		);

		$this->em->persist($subscription);
		$this->em->flush($subscription);
  }
}
