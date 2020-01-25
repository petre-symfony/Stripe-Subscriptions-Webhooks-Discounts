<?php


namespace App\Controller;


use App\Entity\Subscription;
use App\Entity\User;
use App\StripeClient;
use App\Subscription\SubscriptionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController {

	/**
	 * @var StripeClient
	 */
	private $stripeClient;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	/**
	 * @var SubscriptionHelper
	 */
	private $subscriptionHelper;

	public function __construct(
		StripeClient $stripeClient,
		EntityManagerInterface $em,
		SubscriptionHelper $subscriptionHelper
	) {
		$this->stripeClient = $stripeClient;
		$this->em = $em;
		$this->subscriptionHelper = $subscriptionHelper;
	}

	/**
	 * @Route("/profile", name="profile_account")
	 */
	public function accountAction() {
		return $this->render('profile/account.html.twig', [
			'error' => null
		]);
	}

	/**
	 * @Route("/profile/subscription/cancel", name="account_subscription_cancel", methods={"POST"})
	 */
	public function cancelSubscriptionAction(){
		$stripeSubscription = $this->stripeClient->cancelSubscription($this->getUser());

		/** @var Subscription $subscription */
		$subscription = $this->getUser()->getSubscription();
		if($stripeSubscription->status == 'canceled'){
			$subscription->cancel();
		} else {
			$subscription->deactivateSubscription();
		}

		$this->em->persist($subscription);
		$this->em->flush();

		$this->addFlash('success', "Subscription Canceled :(");

		return $this->redirectToRoute('profile_account');
	}

	/**
	 * @Route("/profile/subscription/reactivate", name="account_subscription_reactivate", methods={"POST"})
	 */
	public function reactivateSubscriptionAction(){
		$stripeSubscription = $this
			->stripeClient->reactivateSubscription($this->getUser());

		$this->subscriptionHelper->addSubscriptionToUser($stripeSubscription, $this->getUser());

		$this->addFlash('success', 'Welcome back!');

		return $this->redirectToRoute('profile_account');
	}
}