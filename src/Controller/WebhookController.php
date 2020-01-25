<?php

namespace App\Controller;

use App\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController{
	/**
	 * @var StripeClient
	 */
	private $stripeClient;

	public function __construct(StripeClient $stripeClient) {
		$this->stripeClient = $stripeClient;
	}

	/**
   * @Route("/webhooks/stripe", name="webhook_stripe")
   */
  public function stripeWebhookAction(Request $request): Response{
  	$data = json_decode($request->getContent(), true);

  	if($data === null){
  		throw new \Exception('Bad JSON body from Stripe!');
	  }

  	$eventId = $data['id'];
  	$stripeEvent = $this->stripeClient->findEvent($eventId);

  	switch($stripeEvent->type){
		  case 'customer.subscription.deleted':
		  	//todo - fully cancel the user subscription
			  break;
		  default:
		  	throw new \Exception('Unexpected webhook from stripe' . $stripeEvent->type);
	  }
    return new Response('Event Handled: '.$stripeEvent->type);
  }
}
