<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
    public function home(): Response
    {
        return $this->render('shop/home.html.twig');
    }
    
    #[Route('/shop/about', name: 'shop_about')]
    public function about(): Response
    {
        return $this->render('shop/about.html.twig');
    }

    #[Route('/all-products', name: 'shop_all_products')]
    public function allProducts(): Response
    {
        return $this->render('shop/all_products.html.twig');
    }

    #[Route('/allsearch', name: 'shop_allsearch')]
    public function allSearch(): Response
    {
        // 别名路由，指向同一个页面
        return $this->render('shop/all_products.html.twig');
    }

    #[Route('/hot-sales', name: 'shop_hot_sales')]
    public function hotSales(): Response
    {
        return $this->render('shop/hot_sales.html.twig');
    }

    #[Route('/discount', name: 'shop_discount')]
    public function discount(): Response
    {
        return $this->render('shop/discount.html.twig');
    }

    #[Route('/discount-sale', name: 'shop_discount_sale')]
    public function discountSale(): Response
    {
        // 别名路由，指向同一个页面
        return $this->render('shop/discount.html.twig');
    }

    #[Route('/new', name: 'shop_new')]
    public function newProducts(): Response
    {
        return $this->render('shop/new.html.twig');
    }

    #[Route('/direct-delivery', name: 'shop_direct_delivery')]
    public function directDelivery(): Response
    {
        return $this->render('shop/direct_delivery.html.twig');
    }

    #[Route('/all-categories', name: 'shop_all_categories')]
    public function allCategories(): Response
    {
        return $this->render('shop/all_categories.html.twig');
    }
}