<?php


namespace App\Controller;


use App\Entity\Subscription;
use App\StripeClient;
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

	public function __construct(
		StripeClient $stripeClient,
		EntityManagerInterface $em
	) {
		$this->stripeClient = $stripeClient;
		$this->em = $em;
	}

	/**
	 * @Route("/profile", name="profile_account")
	 */
	public function accountAction() {
		return $this->render('profile/account.html.twig');
	}

	/**
	 * @Route("/profile/subscription/cancel", name="account_subscription_cancel", methods={"POST"})
	 */
	public function cancelSubscriptionAction(){
		$this->stripeClient->cancelSubscription($this->getUser());

		/** @var Subscription $subscription */
		$subscription = $this->getUser()->getSubscription();
		$subscription->deactivateSubscription();
		$this->em->persist($subscription);
		$this->em->flush();

		$this->addFlash('success', "Subscription Canceled :(");

		return $this->redirectToRoute('profile_account');
	}

	/**
	 * @Route("/profile/subscription/reactivate", name="account_subscription_reactivate", methods={"POST"})
	 */
	public function reactivateSubscriptionAction(){

	}
}