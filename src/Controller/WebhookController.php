<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController{
  /**
   * @Route("/webhooks/stripe", name="webhook_stripe")
   */
  public function stripeWebhookAction(): Response{
    return new Response('baaa');
  }
}
