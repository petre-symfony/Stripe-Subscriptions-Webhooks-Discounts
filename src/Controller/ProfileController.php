<?php


namespace App\Controller;


use App\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController {

	/**
	 * @var StripeClient
	 */
	private $stripeClient;

	public function __construct(StripeClient $stripeClient) {
		$this->stripeClient = $stripeClient;
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
		$this->addFlash('success', "Subscription Canceled :(");

		return $this->redirectToRoute('profile_account');
	}
}