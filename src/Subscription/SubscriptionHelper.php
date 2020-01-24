<?php

namespace App\Subscription;

class SubscriptionHelper{
  /** @var SubscriptionPlan[] */
  private $plans = [];

  public function __construct() {
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
}
