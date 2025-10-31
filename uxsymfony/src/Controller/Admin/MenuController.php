<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\MenuCategory;
use App\Entity\MenuSubcategory;
use App\Entity\MenuItem;
use App\Entity\PromotionMenu;
use App\Repository\MenuCategoryRepository;
use App\Repository\MenuSubcategoryRepository;
use App\Repository\MenuItemRepository;
use App\Repository\PromotionMenuRepository;
use App\Service\PathConfigService;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin{adminLoginPath}', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class MenuController extends AbstractController
{
    #[Route('/menu/category/list/tab', name: 'admin_menu_category_list_tab')]
    public function menuCategoryListTab(
        string $adminLoginPath,
        MenuCategoryRepository $menuCategoryRepository,
        Request $request
    ): Response {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取菜单分类列表（分页）
        $menuCategories = $menuCategoryRepository->findBy([], ['sortOrder' => 'ASC'], $limit, $offset);
        $totalCategories = count($menuCategoryRepository->findAll());
        $totalPages = ceil($totalCategories / $limit);
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_menu_category_list_inner.html.twig', [
            'menuCategories' => $menuCategories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCategories' => $totalCategories,
        ]);
    }
    
    #[Route('/menu/category/list/data', name: 'admin_menu_category_list_data')]
    public function menuCategoryListData(
        string $adminLoginPath,
        MenuCategoryRepository $menuCategoryRepository,
        Request $request
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取菜单分类列表（分页）
        $menuCategories = $menuCategoryRepository->findBy([], ['sortOrder' => 'ASC'], $limit, $offset);
        $totalCategories = count($menuCategoryRepository->findAll());
        $totalPages = ceil($totalCategories / $limit);
        
        // 转换数据格式
        $categoriesData = [];
        foreach ($menuCategories as $category) {
            $categoriesData[] = [
                'id' => $category->getId(),
                'title' => $category->getTitle(),
                'titleEn' => $category->getTitleEn(),
                'icon' => $category->getIcon(),
                'sortOrder' => $category->getSortOrder(),
                'isActive' => $category->isActive(),
                'createdAt' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $category->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => [
                'menuCategories' => $categoriesData,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalCategories' => $totalCategories,
            ]
        ]);
    }
    
    #[Route('/menu/category/create', name: 'admin_menu_category_create', methods: ['POST'])]
    public function createMenuCategory(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        // 获取请求数据
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $icon = $request->request->get('icon', 'Home');
        $sortOrder = (int) $request->request->get('sortOrder', 0);
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 创建新的菜单分类
        $menuCategory = new MenuCategory();
        $menuCategory->setTitle($title);
        $menuCategory->setTitleEn($titleEn);
        $menuCategory->setIcon($icon);
        $menuCategory->setSortOrder($sortOrder);
        $menuCategory->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($menuCategory);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->persist($menuCategory);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单分类创建成功',
            'data' => [
                'id' => $menuCategory->getId(),
                'title' => $menuCategory->getTitle(),
                'titleEn' => $menuCategory->getTitleEn(),
                'icon' => $menuCategory->getIcon(),
                'sortOrder' => $menuCategory->getSortOrder(),
                'isActive' => $menuCategory->isActive(),
                'createdAt' => $menuCategory->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $menuCategory->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/category/update/{id}', name: 'admin_menu_category_update', methods: ['POST'])]
    public function updateMenuCategory(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuCategoryRepository $menuCategoryRepository,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        // 查找菜单分类
        $menuCategory = $menuCategoryRepository->find($id);
        if (!$menuCategory) {
            return new JsonResponse(['success' => false, 'message' => '菜单分类不存在'], 404);
        }
        
        // 获取请求数据
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $icon = $request->request->get('icon', 'Home');
        $sortOrder = (int) $request->request->get('sortOrder', 0);
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 更新菜单分类
        $menuCategory->setTitle($title);
        $menuCategory->setTitleEn($titleEn);
        $menuCategory->setIcon($icon);
        $menuCategory->setSortOrder($sortOrder);
        $menuCategory->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($menuCategory);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单分类更新成功',
            'data' => [
                'id' => $menuCategory->getId(),
                'title' => $menuCategory->getTitle(),
                'titleEn' => $menuCategory->getTitleEn(),
                'icon' => $menuCategory->getIcon(),
                'sortOrder' => $menuCategory->getSortOrder(),
                'isActive' => $menuCategory->isActive(),
                'createdAt' => $menuCategory->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $menuCategory->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/category/delete/{id}', name: 'admin_menu_category_delete', methods: ['POST'])]
    public function deleteMenuCategory(
        int $id,
        EntityManagerInterface $entityManager,
        MenuCategoryRepository $menuCategoryRepository,
        MenuSubcategoryRepository $menuSubcategoryRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        // 查找菜单分类
        $menuCategory = $menuCategoryRepository->find($id);
        if (!$menuCategory) {
            return new JsonResponse(['success' => false, 'message' => '菜单分类不存在'], 404);
        }
        
        // 检查是否有关联的二级分类
        $subcategories = $menuSubcategoryRepository->findBy(['categoryId' => $id]);
        if (count($subcategories) > 0) {
            return new JsonResponse(['success' => false, 'message' => '该分类下有关联的二级分类，无法删除'], 400);
        }
        
        // 删除菜单分类
        $entityManager->remove($menuCategory);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单分类删除成功'
        ]);
    }
    
    #[Route('/menu/subcategory/list/tab', name: 'admin_menu_subcategory_list_tab')]
    public function menuSubcategoryListTab(
        string $adminLoginPath,
        MenuSubcategoryRepository $menuSubcategoryRepository,
        Request $request
    ): Response {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取菜单子分类列表（分页）
        $menuSubcategories = $menuSubcategoryRepository->findBy([], ['sortOrder' => 'ASC'], $limit, $offset);
        $totalSubcategories = count($menuSubcategoryRepository->findAll());
        $totalPages = ceil($totalSubcategories / $limit);
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_menu_subcategory_list_inner.html.twig', [
            'menuSubcategories' => $menuSubcategories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalSubcategories' => $totalSubcategories,
        ]);
    }
    
    #[Route('/menu/subcategory/list/data', name: 'admin_menu_subcategory_list_data')]
    public function menuSubcategoryListData(
        string $adminLoginPath,
        MenuSubcategoryRepository $menuSubcategoryRepository,
        Request $request
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取分类ID参数（可选）
        $categoryId = $request->query->getInt('categoryId');
        
        // 构建查询条件
        $criteria = [];
        if ($categoryId > 0) {
            $criteria['categoryId'] = $categoryId;
        }
        
        // 获取菜单子分类列表（分页）
        $menuSubcategories = $menuSubcategoryRepository->findBy($criteria, ['sortOrder' => 'ASC'], $limit, $offset);
        $totalSubcategories = count($menuSubcategoryRepository->findBy($criteria));
        $totalPages = ceil($totalSubcategories / $limit);
        
        // 转换数据格式
        $subcategoriesData = [];
        foreach ($menuSubcategories as $subcategory) {
            $subcategoriesData[] = [
                'id' => $subcategory->getId(),
                'categoryId' => $subcategory->getCategoryId(),
                'categoryTitle' => $subcategory->getCategory() ? $subcategory->getCategory()->getTitle() : '',
                'title' => $subcategory->getTitle(),
                'titleEn' => $subcategory->getTitleEn(),
                'sortOrder' => $subcategory->getSortOrder(),
                'isActive' => $subcategory->isActive(),
                'createdAt' => $subcategory->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $subcategory->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => [
                'menuSubcategories' => $subcategoriesData,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalSubcategories' => $totalSubcategories,
            ]
        ]);
    }
    
    #[Route('/menu/subcategory/create', name: 'admin_menu_subcategory_create', methods: ['POST'])]
    public function createMenuSubcategory(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuCategoryRepository $menuCategoryRepository,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取请求数据
        $categoryId = (int) $request->request->get('categoryId');
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $sortOrder = (int) $request->request->get('sortOrder', 0);
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 查找父级分类
        $category = $menuCategoryRepository->find($categoryId);
        if (!$category) {
            return new JsonResponse(['success' => false, 'message' => '父级分类不存在'], 404);
        }
        
        // 创建新的菜单子分类
        $menuSubcategory = new MenuSubcategory();
        $menuSubcategory->setCategory($category);
        $menuSubcategory->setCategoryId($categoryId);
        $menuSubcategory->setTitle($title);
        $menuSubcategory->setTitleEn($titleEn);
        $menuSubcategory->setSortOrder($sortOrder);
        $menuSubcategory->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($menuSubcategory);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->persist($menuSubcategory);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单子分类创建成功',
            'data' => [
                'id' => $menuSubcategory->getId(),
                'categoryId' => $menuSubcategory->getCategoryId(),
                'categoryTitle' => $menuSubcategory->getCategory() ? $menuSubcategory->getCategory()->getTitle() : '',
                'title' => $menuSubcategory->getTitle(),
                'titleEn' => $menuSubcategory->getTitleEn(),
                'sortOrder' => $menuSubcategory->getSortOrder(),
                'isActive' => $menuSubcategory->isActive(),
                'createdAt' => $menuSubcategory->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $menuSubcategory->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/subcategory/update/{id}', name: 'admin_menu_subcategory_update', methods: ['POST'])]
    public function updateMenuSubcategory(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuSubcategoryRepository $menuSubcategoryRepository,
        MenuCategoryRepository $menuCategoryRepository,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 查找菜单子分类
        $menuSubcategory = $menuSubcategoryRepository->find($id);
        if (!$menuSubcategory) {
            return new JsonResponse(['success' => false, 'message' => '菜单子分类不存在'], 404);
        }
        
        // 获取请求数据
        $categoryId = (int) $request->request->get('categoryId');
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $sortOrder = (int) $request->request->get('sortOrder', 0);
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 查找父级分类
        $category = $menuCategoryRepository->find($categoryId);
        if (!$category) {
            return new JsonResponse(['success' => false, 'message' => '父级分类不存在'], 404);
        }
        
        // 更新菜单子分类
        $menuSubcategory->setCategory($category);
        $menuSubcategory->setCategoryId($categoryId);
        $menuSubcategory->setTitle($title);
        $menuSubcategory->setTitleEn($titleEn);
        $menuSubcategory->setSortOrder($sortOrder);
        $menuSubcategory->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($menuSubcategory);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单子分类更新成功',
            'data' => [
                'id' => $menuSubcategory->getId(),
                'categoryId' => $menuSubcategory->getCategoryId(),
                'categoryTitle' => $menuSubcategory->getCategory() ? $menuSubcategory->getCategory()->getTitle() : '',
                'title' => $menuSubcategory->getTitle(),
                'titleEn' => $menuSubcategory->getTitleEn(),
                'sortOrder' => $menuSubcategory->getSortOrder(),
                'isActive' => $menuSubcategory->isActive(),
                'createdAt' => $menuSubcategory->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $menuSubcategory->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/subcategory/delete/{id}', name: 'admin_menu_subcategory_delete', methods: ['POST'])]
    public function deleteMenuSubcategory(
        int $id,
        EntityManagerInterface $entityManager,
        MenuSubcategoryRepository $menuSubcategoryRepository,
        MenuItemRepository $menuItemRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 查找菜单子分类
        $menuSubcategory = $menuSubcategoryRepository->find($id);
        if (!$menuSubcategory) {
            return new JsonResponse(['success' => false, 'message' => '菜单子分类不存在'], 404);
        }
        
        // 检查是否有关联的三级分类
        $menuItems = $menuItemRepository->findBy(['subCategoryId' => $id]);
        if (count($menuItems) > 0) {
            return new JsonResponse(['success' => false, 'message' => '该分类下有关联的三级分类，无法删除'], 400);
        }
        
        // 删除菜单子分类
        $entityManager->remove($menuSubcategory);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单子分类删除成功'
        ]);
    }
    
    #[Route('/menu/item/list/tab', name: 'admin_menu_item_list_tab')]
    public function menuItemListTab(
        string $adminLoginPath,
        MenuItemRepository $menuItemRepository,
        Request $request
    ): Response {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取菜单项列表（分页）
        $menuItems = $menuItemRepository->findBy([], ['sortOrder' => 'ASC'], $limit, $offset);
        $totalItems = count($menuItemRepository->findAll());
        $totalPages = ceil($totalItems / $limit);
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_menu_item_list_inner.html.twig', [
            'menuItems' => $menuItems,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }
    
    #[Route('/menu/item/list/data', name: 'admin_menu_item_list_data')]
    public function menuItemListData(
        string $adminLoginPath,
        MenuItemRepository $menuItemRepository,
        Request $request
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取分类ID参数（可选）
        $subcategoryId = $request->query->getInt('subcategoryId');
        
        // 构建查询条件
        $criteria = [];
        if ($subcategoryId > 0) {
            $criteria['subCategoryId'] = $subcategoryId;
        }
        
        // 获取菜单项列表（分页）
        $menuItems = $menuItemRepository->findBy($criteria, ['sortOrder' => 'ASC'], $limit, $offset);
        $totalItems = count($menuItemRepository->findBy($criteria));
        $totalPages = ceil($totalItems / $limit);
        
        // 转换数据格式
        $itemsData = [];
        foreach ($menuItems as $item) {
            $itemsData[] = [
                'id' => $item->getId(),
                'subcategoryId' => $item->getSubCategoryId(),
                'subcategoryTitle' => $item->getSubcategory() ? $item->getSubcategory()->getTitle() : '',
                'title' => $item->getTitle(),
                'titleEn' => $item->getTitleEn(),
                'sortOrder' => $item->getSortOrder(),
                'isActive' => $item->isActive(),
                'createdAt' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $item->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => [
                'menuItems' => $itemsData,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
            ]
        ]);
    }
    
    #[Route('/menu/item/create', name: 'admin_menu_item_create', methods: ['POST'])]
    public function createMenuItem(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuSubcategoryRepository $menuSubcategoryRepository,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取请求数据
        $subcategoryId = (int) $request->request->get('subcategoryId');
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $sortOrder = (int) $request->request->get('sortOrder', 0);
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 查找父级分类
        $subcategory = $menuSubcategoryRepository->find($subcategoryId);
        if (!$subcategory) {
            return new JsonResponse(['success' => false, 'message' => '父级分类不存在'], 404);
        }
        
        // 创建新的菜单项
        $menuItem = new MenuItem();
        $menuItem->setSubcategory($subcategory);
        $menuItem->setSubCategoryId($subcategoryId);
        $menuItem->setTitle($title);
        $menuItem->setTitleEn($titleEn);
        $menuItem->setSortOrder($sortOrder);
        $menuItem->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($menuItem);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->persist($menuItem);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单项创建成功',
            'data' => [
                'id' => $menuItem->getId(),
                'subcategoryId' => $menuItem->getSubCategoryId(),
                'subcategoryTitle' => $menuItem->getSubcategory() ? $menuItem->getSubcategory()->getTitle() : '',
                'title' => $menuItem->getTitle(),
                'titleEn' => $menuItem->getTitleEn(),
                'sortOrder' => $menuItem->getSortOrder(),
                'isActive' => $menuItem->isActive(),
                'createdAt' => $menuItem->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $menuItem->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/item/update/{id}', name: 'admin_menu_item_update', methods: ['POST'])]
    public function updateMenuItem(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuItemRepository $menuItemRepository,
        MenuSubcategoryRepository $menuSubcategoryRepository,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 查找菜单项
        $menuItem = $menuItemRepository->find($id);
        if (!$menuItem) {
            return new JsonResponse(['success' => false, 'message' => '菜单项不存在'], 404);
        }
        
        // 获取请求数据
        $subcategoryId = (int) $request->request->get('subcategoryId');
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $sortOrder = (int) $request->request->get('sortOrder', 0);
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 查找父级分类
        $subcategory = $menuSubcategoryRepository->find($subcategoryId);
        if (!$subcategory) {
            return new JsonResponse(['success' => false, 'message' => '父级分类不存在'], 404);
        }
        
        // 更新菜单项
        $menuItem->setSubcategory($subcategory);
        $menuItem->setSubCategoryId($subcategoryId);
        $menuItem->setTitle($title);
        $menuItem->setTitleEn($titleEn);
        $menuItem->setSortOrder($sortOrder);
        $menuItem->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($menuItem);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单项更新成功',
            'data' => [
                'id' => $menuItem->getId(),
                'subcategoryId' => $menuItem->getSubCategoryId(),
                'subcategoryTitle' => $menuItem->getSubcategory() ? $menuItem->getSubcategory()->getTitle() : '',
                'title' => $menuItem->getTitle(),
                'titleEn' => $menuItem->getTitleEn(),
                'sortOrder' => $menuItem->getSortOrder(),
                'isActive' => $menuItem->isActive(),
                'createdAt' => $menuItem->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $menuItem->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/item/delete/{id}', name: 'admin_menu_item_delete', methods: ['POST'])]
    public function deleteMenuItem(
        int $id,
        EntityManagerInterface $entityManager,
        MenuItemRepository $menuItemRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 查找菜单项
        $menuItem = $menuItemRepository->find($id);
        if (!$menuItem) {
            return new JsonResponse(['success' => false, 'message' => '菜单项不存在'], 404);
        }
        
        // 删除菜单项
        $entityManager->remove($menuItem);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '菜单项删除成功'
        ]);
    }
    
    #[Route('/menu/item/batch-delete', name: 'admin_menu_item_batch_delete', methods: ['POST'])]
    public function batchDeleteMenuItems(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuItemRepository $menuItemRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取请求中的ID列表
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            return new JsonResponse(['success' => false, 'message' => '无效的ID列表'], 400);
        }
        
        // 查找并删除指定的菜单项
        $deletedCount = 0;
        foreach ($ids as $id) {
            $menuItem = $menuItemRepository->find($id);
            if ($menuItem) {
                $entityManager->remove($menuItem);
                $deletedCount++;
            }
        }
        
        // 保存更改
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '批量删除成功',
            'deletedCount' => $deletedCount
        ]);
    }
    
    #[Route('/menu/promotion/list/tab', name: 'admin_menu_promotion_list_tab')]
    public function menuPromotionListTab(
        string $adminLoginPath,
        PromotionMenuRepository $promotionMenuRepository,
        Request $request
    ): Response {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取促销菜单列表（分页）
        $promotionMenus = $promotionMenuRepository->findBy([], ['id' => 'ASC'], $limit, $offset);
        $totalMenus = count($promotionMenuRepository->findAll());
        $totalPages = ceil($totalMenus / $limit);
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_menu_promotion_list_inner.html.twig', [
            'promotionMenus' => $promotionMenus,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalMenus' => $totalMenus,
        ]);
    }
    
    #[Route('/menu/promotion/list/data', name: 'admin_menu_promotion_list_data')]
    public function menuPromotionListData(
        string $adminLoginPath,
        PromotionMenuRepository $promotionMenuRepository,
        Request $request
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // 获取分类ID参数（可选）
        $categoryId = $request->query->getInt('categoryId');
        
        // 构建查询条件
        $criteria = [];
        if ($categoryId > 0) {
            $criteria['categoryId'] = $categoryId;
        }
        
        // 获取促销菜单列表（分页）
        $promotionMenus = $promotionMenuRepository->findBy($criteria, ['id' => 'ASC'], $limit, $offset);
        $totalMenus = count($promotionMenuRepository->findBy($criteria));
        $totalPages = ceil($totalMenus / $limit);
        
        // 创建七牛云服务实例用于生成私有链接
        try {
            $qiniuService = new QiniuUploadService();
        } catch (\Exception $e) {
            // 如果七牛云服务初始化失败，记录错误但继续返回数据（不包含图片URL）
            error_log('七牛云服务初始化失败: ' . $e->getMessage());
            $qiniuService = null;
        }
        
        // 转换数据格式
        $menusData = [];
        foreach ($promotionMenus as $menu) {
            $imageKey = $menu->getImageUrl();  // 数据库存储的key
            $imageUrl = '';
            
            // 只有在七牛云服务可用时才生成签名URL
            if ($qiniuService && $imageKey) {
                // 如果已经是完整URL（兼容旧数据），提取key后重新生成签名URL
                if (str_starts_with($imageKey, 'http')) {
                    $parts = parse_url($imageKey);
                    if (isset($parts['path'])) {
                        $imageKey = ltrim($parts['path'], '/');
                        // 移除查询参数中的签名
                        $imageKey = preg_replace('/\?.*$/', '', $imageKey);
                    }
                }
                
                try {
                    // 生成新的签名URL
                    $imageUrl = $qiniuService->getPrivateUrl($imageKey);
                } catch (\Exception $e) {
                    error_log('生成签名URL失败 (key: ' . $imageKey . '): ' . $e->getMessage());
                    $imageUrl = '';
                }
            }
            
            $menusData[] = [
                'id' => $menu->getId(),
                'categoryId' => $menu->getCategoryId(),
                'categoryTitle' => $menu->getCategory() ? $menu->getCategory()->getTitle() : '',
                'title' => $menu->getTitle(),
                'titleEn' => $menu->getTitleEn(),
                'imageUrl' => $imageUrl,  // 返回带签名的URL用于显示
                'imageKey' => $imageKey,  // 返回原始key用于编辑
                'isActive' => $menu->isActive(),
                'createdAt' => $menu->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $menu->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => [
                'promotionMenus' => $menusData,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalMenus' => $totalMenus,
            ]
        ]);
    }
    
    #[Route('/menu/promotion/create', name: 'admin_menu_promotion_create', methods: ['POST'])]
    public function createMenuPromotion(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuCategoryRepository $menuCategoryRepository,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取请求数据
        $categoryId = (int) $request->request->get('categoryId');
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $imageUrl = $request->request->get('imageUrl');
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 查找父级分类
        $category = $menuCategoryRepository->find($categoryId);
        if (!$category) {
            return new JsonResponse(['success' => false, 'message' => '父级分类不存在'], 404);
        }
        
        // 创建新的促销菜单
        $promotionMenu = new PromotionMenu();
        $promotionMenu->setCategory($category);
        $promotionMenu->setCategoryId($categoryId);
        $promotionMenu->setTitle($title);
        $promotionMenu->setTitleEn($titleEn);
        $promotionMenu->setImageUrl($imageUrl);  // 存储的是key而不是完整URL
        $promotionMenu->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($promotionMenu);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->persist($promotionMenu);
        $entityManager->flush();
        
        // 生成带签名的私有URL用于返回
        $displayImageUrl = $imageUrl;  // 默认使用原始key
        try {
            $qiniuService = new QiniuUploadService();
            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                $displayImageUrl = $qiniuService->getPrivateUrl($imageUrl);
            }
        } catch (\Exception $e) {
            error_log('生成签名URL失败: ' . $e->getMessage());
        }
        
        return new JsonResponse([
            'success' => true,
            'message' => '促销菜单创建成功',
            'data' => [
                'id' => $promotionMenu->getId(),
                'categoryId' => $promotionMenu->getCategoryId(),
                'categoryTitle' => $promotionMenu->getCategory() ? $promotionMenu->getCategory()->getTitle() : '',
                'title' => $promotionMenu->getTitle(),
                'titleEn' => $promotionMenu->getTitleEn(),
                'imageUrl' => $displayImageUrl,  // 返回带签名的URL用于显示
                'imageKey' => $imageUrl,  // 返回原始key
                'isActive' => $promotionMenu->isActive(),
                'createdAt' => $promotionMenu->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $promotionMenu->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/promotion/update/{id}', name: 'admin_menu_promotion_update', methods: ['POST'])]
    public function updateMenuPromotion(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        PromotionMenuRepository $promotionMenuRepository,
        MenuCategoryRepository $menuCategoryRepository,
        ValidatorInterface $validator,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 查找促销菜单
        $promotionMenu = $promotionMenuRepository->find($id);
        if (!$promotionMenu) {
            return new JsonResponse(['success' => false, 'message' => '促销菜单不存在'], 404);
        }
        
        // 获取请求数据
        $categoryId = (int) $request->request->get('categoryId');
        $title = $request->request->get('title');
        $titleEn = $request->request->get('titleEn');
        $imageUrl = $request->request->get('imageUrl');
        $isActive = (bool) $request->request->get('isActive', true);
        
        // 查找父级分类
        $category = $menuCategoryRepository->find($categoryId);
        if (!$category) {
            return new JsonResponse(['success' => false, 'message' => '父级分类不存在'], 404);
        }
        
        // 更新促销菜单
        $promotionMenu->setCategory($category);
        $promotionMenu->setCategoryId($categoryId);
        $promotionMenu->setTitle($title);
        $promotionMenu->setTitleEn($titleEn);
        $promotionMenu->setImageUrl($imageUrl);  // 存储的是key而不是完整URL
        $promotionMenu->setIsActive($isActive);
        
        // 验证数据
        $errors = $validator->validate($promotionMenu);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages
            ]);
        }
        
        // 保存到数据库
        $entityManager->flush();
        
        // 生成带签名的私有URL用于返回
        $displayImageUrl = $imageUrl;  // 默认使用原始key
        try {
            $qiniuService = new QiniuUploadService();
            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                $displayImageUrl = $qiniuService->getPrivateUrl($imageUrl);
            }
        } catch (\Exception $e) {
            error_log('生成签名URL失败: ' . $e->getMessage());
        }
        
        return new JsonResponse([
            'success' => true,
            'message' => '促销菜单更新成功',
            'data' => [
                'id' => $promotionMenu->getId(),
                'categoryId' => $promotionMenu->getCategoryId(),
                'categoryTitle' => $promotionMenu->getCategory() ? $promotionMenu->getCategory()->getTitle() : '',
                'title' => $promotionMenu->getTitle(),
                'titleEn' => $promotionMenu->getTitleEn(),
                'imageUrl' => $displayImageUrl,  // 返回带签名的URL用于显示
                'imageKey' => $imageUrl,  // 返回原始key
                'isActive' => $promotionMenu->isActive(),
                'createdAt' => $promotionMenu->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $promotionMenu->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    #[Route('/menu/promotion/delete/{id}', name: 'admin_menu_promotion_delete', methods: ['POST'])]
    public function deleteMenuPromotion(
        int $id,
        EntityManagerInterface $entityManager,
        PromotionMenuRepository $promotionMenuRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 查找促销菜单
        $promotionMenu = $promotionMenuRepository->find($id);
        if (!$promotionMenu) {
            return new JsonResponse(['success' => false, 'message' => '促销菜单不存在'], 404);
        }
        
        // 删除促销菜单
        $entityManager->remove($promotionMenu);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '促销菜单删除成功'
        ]);
    }
    
    #[Route('/promotion/image/signed-url', name: 'admin_promotion_image_signed_url', methods: ['POST'])]
    public function getPromotionImageSignedUrl(
        Request $request,
        string $adminLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }
        
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SITEMENU角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        try {
            $data = json_decode($request->getContent(), true);
            $key = $data['key'] ?? '';
            
            if (empty($key)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '缺少图片key参数'
                ], 400);
            }
            
            // 如果已经是完整URL，直接返回
            if (str_starts_with($key, 'http')) {
                return new JsonResponse([
                    'success' => true,
                    'url' => $key
                ]);
            }
            
            // 创建七牛云服务实例
            $qiniuService = new QiniuUploadService();
            
            // 生成带签名的私有URL
            $signedUrl = $qiniuService->getPrivateUrl($key);
            
            return new JsonResponse([
                'success' => true,
                'url' => $signedUrl
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '生成签名URL失败: ' . $e->getMessage()
            ], 500);
        }
    }

}