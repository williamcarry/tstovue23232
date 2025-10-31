<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutSaleyeeController extends AbstractController
{
    #[Route('/about-saleyee', name: 'shop_about_saleyee')]
    public function index(): Response
    {
        // 渲染关于赛盈页面的 Vue 组件
        return $this->render('shop/about_saleyee.html.twig');
    }
}