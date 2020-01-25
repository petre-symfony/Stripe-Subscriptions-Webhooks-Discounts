<?php

namespace App\Tests;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class WebhookControllerTest extends WebTestCase {
	/** @var EntityManagerInterface */
	private $em;

	private $client;

	public function setup(){
		$this->client = static::createClient();
		$this->em = static::$container->get(EntityManagerInterface::class);
	}

	public function testStripeCustomerSubscriptionDeleted(){
		$subscription = $this->createSubscription();

		$eventJson = $this->getCustomerSubscriptionDeletedEvent(
			$subscription->getStripeSubscriptionId()
		);

		$this->client->request(
			"POST",
			'webhooks/stripe',
			[],
			[],
			[],
			$eventJson
		);
		dd($this->client->getResponse()->getContent());

		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->assertFalse($subscription->isActive());
	}
	
  private function createSubscription(){
		$user = new User();
		$user->setEmail('fluffy'.mt_rand().'@sheep.com');
	  $encoded = static::$container
		  ->get(UserPasswordEncoderInterface::class)
		  ->encodePassword($user, 'baa');
	  $user->setPassword($encoded);
	  $this->em->persist($user);

	  $subscription = new Subscription();
	  $subscription->setUser($user);
	  $subscription->activateSubscription(
		  'plan_STRIPE_TEST_ABC'.mt_rand(),
		  'sub_STRIPE_TEST_XYZ'.mt_rand(),
		   new \DateTime('+1 month')
	  );

	  $this->em->persist($subscription);
	  $this->em->flush();

	  return $subscription;
  }

	private function getCustomerSubscriptionDeletedEvent($subscriptionId){
		$json = <<<EOF
		{
      "created": 1326853478,
      "livemode": false,
      "id": "evt_00000000000000",
      "type": "customer.subscription.deleted",
      "object": "event",
      "request": null,
      "pending_webhooks": 1,
      "api_version": "2017-02-14",
      "data": {
        "object": {
            "id": "%s",
            "object": "subscription",
            "application_fee_percent": null,
            "billing": "charge_automatically",
            "billing_cycle_anchor": 1579883474,
            "billing_thresholds": null,
            "cancel_at": null,
            "cancel_at_period_end": false,
            "canceled_at": null,
            "collection_method": "charge_automatically",
            "created": 1579883474,
            "current_period_end": 1582561874,
            "current_period_start": 1579883474,
            "customer": "cus_00000000000000",
            "days_until_due": null,
            "default_payment_method": null,
            "default_source": null,
            "default_tax_rates": [],
            "discount": null,
            "ended_at": 1579944968,
            "invoice_customer_balance_settings": {
                "consume_applied_balance_on_void": true
            },
            "items": {
                "object": "list",
                "data": [
                    {
                        "id": "si_00000000000000",
                        "object": "subscription_item",
                        "billing_thresholds": null,
                        "created": 1579883474,
                        "metadata": {},
                        "plan": {
                            "id": "plan_00000000000000",
                            "object": "plan",
                            "active": true,
                            "aggregate_usage": null,
                            "amount": 9900,
                            "amount_decimal": "9900",
                            "billing_scheme": "per_unit",
                            "created": 1579847533,
                            "currency": "usd",
                            "interval": "month",
                            "interval_count": 1,
                            "livemode": false,
                            "metadata": {},
                            "name": "farmer_brent",
                            "nickname": "farmer_brent_monthly",
                            "product": "prod_00000000000000",
                            "statement_descriptor": null,
                            "tiers": null,
                            "tiers_mode": null,
                            "transform_usage": null,
                            "trial_period_days": null,
                            "usage_type": "licensed"
                        },
                        "quantity": 1,
                        "subscription": "sub_00000000000000",
                        "tax_rates": []
                    }
                ],
                "has_more": false,
                "url": "/v1/subscription_items?subscription=sub_GbiU6XOGM32zzy"
            },
            "latest_invoice": "in_1G4V3u26uSzZng1kYru1gy6d",
            "livemode": false,
            "metadata": {},
            "next_pending_invoice_item_invoice": null,
            "pending_invoice_item_interval": null,
            "pending_setup_intent": null,
            "pending_update": null,
            "plan": {
                "id": "plan_00000000000000",
                "object": "plan",
                "active": true,
                "aggregate_usage": null,
                "amount": 9900,
                "amount_decimal": "9900",
                "billing_scheme": "per_unit",
                "created": 1579847533,
                "currency": "usd",
                "interval": "month",
                "interval_count": 1,
                "livemode": false,
                "metadata": {},
                "name": "farmer_brent",
                "nickname": "farmer_brent_monthly",
                "product": "prod_00000000000000",
                "statement_descriptor": null,
                "tiers": null,
                "tiers_mode": null,
                "transform_usage": null,
                "trial_period_days": null,
                "usage_type": "licensed"
            },
            "quantity": 1,
            "schedule": null,
            "start": 1579938984,
            "start_date": 1579883474,
            "status": "canceled",
            "tax_percent": null,
            "trial_end": null,
            "trial_start": null
        }
      }
    }
EOF;
		return sprintf($json, $subscriptionId);
	}
}
