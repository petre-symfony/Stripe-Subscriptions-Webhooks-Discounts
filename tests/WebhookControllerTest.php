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

		//to do send the cancelation webhook

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
}
