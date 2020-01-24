<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController {
	/**
	 * @Route("/profile", name="profile_account")
	 */
	public function accountAction() {
		return $this->render('profile/account.html.twig');
	}
}