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
			'plan_GcRX0jIdxXlTzh',
			'farmer_brent_yearly',
			'990',
			SubscriptionPlan::DURATION_YEARLY
		);

	  $this->plans[] = new SubscriptionPlan(
		  'plan_GbYrirrvJYKsV7',
		  'new_zeelander_monthly',
		  '199'
	  );

		$this->plans[] = new SubscriptionPlan(
			'plan_GcRZv5SyNgX7x0',
			'new_zeelander_yearly',
			'1990',
			SubscriptionPlan::DURATION_YEARLY
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

	public function findPlanByName($planName){
		foreach ($this->plans as $plan) {
			if ($plan->getName() == $planName) {
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

		$periodEnd = \DateTime::createFromFormat('U', $stripeSubscription->current_period_end);

		$subscription->activateSubscription(
			$stripeSubscription->plan->id,
			$stripeSubscription->id,
			$periodEnd
		);

		$this->em->persist($subscription);
		$this->em->flush($subscription);
  }

	public function updateCardDetails(User $user, \Stripe\Customer $stripeCustomer){
  	if(!$stripeCustomer->sources->data){
  		//customer may not have a card on file
  		return;
	  }
		$cardDetails = $stripeCustomer->sources->data[0];
		$user->setCardBrand($cardDetails->brand);
		$user->setCardLast4($cardDetails->last4);

		$this->em->persist($user);
		$this->em->flush($user);
	}

	public function fullyCancelSubscription(Subscription $subscription){
		$subscription->cancel();
		$this->em->persist($subscription);
		$this->em->flush($subscription);
	}

	public function handleSubscriptionPaid(
		Subscription $subscription,
		\Stripe\Subscription $stripeSubscription
	){
		$newPeriodEnd = \DateTime::createFromFormat('U', $stripeSubscription->current_period_end);

		// send email if renewal
		$isRenewal = $newPeriodEnd > $subscription->getBillingPeriodEndsAt();
		
		$subscription->setBillingPeriodEndsAt($newPeriodEnd);

		$this->em->persist($subscription);
		$this->em->flush($subscription);
	}

	/**
	 * @param $currentPlanName
	 * @return SubscriptionPlan|null
	 */
	public function findPlanToChangeTo($currentPlanName){
		if (strpos($currentPlanName, 'farmer_brent') !== false) {
			$newPlanName = str_replace('farmer_brent', 'new_zeelander', $currentPlanName);
		} else {
			$newPlanName = str_replace('new_zeelander', 'farmer_brent', $currentPlanName);
		}
		return $this->findPlanByName($newPlanName);
	}

	public function findPlanForOtherDuration($currentPlanName){
		if (strpos($currentPlanName, 'monthly') !== false) {
			$newPlanName = str_replace('monthly', 'yearly', $currentPlanName);
		} else {
			$newPlanName = str_replace('yearly', 'monthly', $currentPlanName);
		}
		return $this->findPlanByName($newPlanName);
	}
}
