<?php


namespace App\Controller;


use App\Entity\Subscription;
use App\Entity\User;
use App\StripeClient;
use App\Subscription\SubscriptionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
		$currentPlan = null;
		$otherPlan = null;
		if($this->getUser()->hasActiveSubscription()){
			$currentPlan = $this->subscriptionHelper->findPlan(
				$this->getUser()->getSubscription()->getStripePlanId()
			);
			$otherPlan = $this->subscriptionHelper->findPlanToChangeTo(
				$currentPlan->getName()
			);
		}

		return $this->render('profile/account.html.twig', [
			'error' => null,
			'stripe_public_key' => $this->getParameter('stripe_public_key'),
			'current_plan' => $currentPlan,
			'other_plan' => $otherPlan
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

	/**
	 * @Route("/profile/card/update", name="account_update_credit_card", methods={"POST"})
	 */
	public function updateCreditCardAction(Request $request) {
		$token = $request->request->get('stripeToken');
		$user = $this->getUser();

		try {
		$stripeCustomer = $this->stripeClient
			->updateCustomerCard($user, $token);
		} catch (\Stripe\Exception\CardException $e){
			$error = 'There was a problem charging the card '.$e->getMessage();

			$this->addFlash('error', $error);

			return $this->redirectToRoute('profile_account');
		}

		$this->subscriptionHelper->updateCardDetails($user, $stripeCustomer);

		$this->addFlash('success', 'Card Updated!');

		return $this->redirectToRoute('profile_account');
	}

	/**
	 * @Route("/profile/plan/change/preview/{planId}", name="account_preview_plan_change")
	 */
	public function previewPlanChangeAction($planId){
		$plan = $this->subscriptionHelper->findPlan($planId);

		$stripeInvoice = $this->stripeClient
			->getUpcomingInvoiceForChangedSubscription(
				$this->getUser(),
				$plan
			)
		;

		// contains the pro-rations *plus* the next cycle's amount
		$total = $stripeInvoice->amount_due;

		$total -= $plan->getPrice() * 100;

		return new JsonResponse(['total' => $total / 100]);
	}

	/**
	 * @Route("/profile/plan/change/execute/{planId}", name="account_execute_plan_change")
	 */
	public function changePlanAction($planId){
		$plan = $this->subscriptionHelper->findPlan($planId);

		$stripeSubscription = $this
			->stripeClient
			->changePlan($this->getUser(), $plan);
	}
}