<?php

namespace App\Controller\Supplier;

use App\Entity\BalanceHistory;
use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use App\Service\BalanceHistoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supplier{supplierLoginPath}/balance-history', requirements: ['supplierLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('SUPPLIER_ACTIVE')]
class BalanceHistoryController extends AbstractController
{
    private $balanceHistoryService;
    private $entityManager;

    public function __construct(BalanceHistoryService $balanceHistoryService, EntityManagerInterface $entityManager)
    {
        $this->balanceHistoryService = $balanceHistoryService;
        $this->entityManager = $entityManager;
    }

    /**
     * 获取余额变化记录列表
     */
    #[Route('/list', name: 'supplier_balance_history_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $type = $request->query->get('type');

        // 获取当前登录的供应商（如果是子账号，则获取主账号）
        $currentSupplier = $this->getMainSupplier();

        $repository = $this->entityManager->getRepository(BalanceHistory::class);
        $queryBuilder = $repository->createQueryBuilder('bh');

        // 添加筛选条件 - 只显示当前供应商的记录
        $queryBuilder->andWhere('bh.userType = :userType')
            ->andWhere('bh.userId = :userId')
            ->setParameter('userType', 'supplier')
            ->setParameter('userId', $currentSupplier->getId());

        if ($type) {
            $queryBuilder->andWhere('bh.type = :type')
                ->setParameter('type', $type);
        }

        // 获取总数
        $totalItems = (int) $queryBuilder->select('COUNT(bh.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        // 获取分页数据
        $queryBuilder->select('bh')
            ->orderBy('bh.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $balanceHistories = $queryBuilder->getQuery()->getResult();

        // 格式化数据
        $data = [];
        foreach ($balanceHistories as $history) {
            $data[] = [
                'id' => $history->getId(),
                'userType' => $history->getUserType(),
                'userId' => $history->getUserId(),
                'username' => $currentSupplier->getUsername(),
                'balanceBefore' => $history->getBalanceBefore(),
                'balanceAfter' => $history->getBalanceAfter(),
                'amount' => $history->getAmount(),
                'frozenBalanceBefore' => $history->getFrozenBalanceBefore(),
                'frozenBalanceAfter' => $history->getFrozenBalanceAfter(),
                'frozenAmount' => $history->getFrozenAmount(),
                'type' => $history->getType(),
                'description' => $history->getDescription(),
                'referenceId' => $history->getReferenceId(),
                'createdAt' => $history->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'currentPage' => $page,
                'totalItems' => $totalItems,
                'totalPages' => ceil($totalItems / $limit),
                'itemsPerPage' => $limit,
            ],
        ]);
    }

    /**
     * 根据用户获取余额变化记录
     */
    #[Route('/user', name: 'supplier_balance_history_user', methods: ['GET'])]
    public function getByUser(): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 获取当前登录的供应商（如果是子账号，则获取主账号）
        $currentSupplier = $this->getMainSupplier();

        $balanceHistories = $this->balanceHistoryService->getBalanceHistoryByUser('supplier', $currentSupplier->getId());

        // 格式化数据
        $data = [];
        foreach ($balanceHistories as $history) {
            $data[] = [
                'id' => $history->getId(),
                'userType' => $history->getUserType(),
                'userId' => $history->getUserId(),
                'username' => $currentSupplier->getUsername(),
                'balanceBefore' => $history->getBalanceBefore(),
                'balanceAfter' => $history->getBalanceAfter(),
                'amount' => $history->getAmount(),
                'frozenBalanceBefore' => $history->getFrozenBalanceBefore(),
                'frozenBalanceAfter' => $history->getFrozenBalanceAfter(),
                'frozenAmount' => $history->getFrozenAmount(),
                'type' => $history->getType(),
                'description' => $history->getDescription(),
                'referenceId' => $history->getReferenceId(),
                'createdAt' => $history->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * 根据业务类型获取余额变化记录
     */
    #[Route('/type/{type}', name: 'supplier_balance_history_type', methods: ['GET'])]
    public function getByType(string $type): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 获取当前登录的供应商（如果是子账号，则获取主账号）
        $currentSupplier = $this->getMainSupplier();

        $balanceHistories = $this->balanceHistoryService->getBalanceHistoryByTypeAndUser($type, 'supplier', $currentSupplier->getId());

        // 格式化数据
        $data = [];
        foreach ($balanceHistories as $history) {
            $data[] = [
                'id' => $history->getId(),
                'userType' => $history->getUserType(),
                'userId' => $history->getUserId(),
                'username' => $currentSupplier->getUsername(),
                'balanceBefore' => $history->getBalanceBefore(),
                'balanceAfter' => $history->getBalanceAfter(),
                'amount' => $history->getAmount(),
                'frozenBalanceBefore' => $history->getFrozenBalanceBefore(),
                'frozenBalanceAfter' => $history->getFrozenBalanceAfter(),
                'frozenAmount' => $history->getFrozenAmount(),
                'type' => $history->getType(),
                'description' => $history->getDescription(),
                'referenceId' => $history->getReferenceId(),
                'createdAt' => $history->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * 获取业务类型映射
     */
    #[Route('/type-mapping', name: 'supplier_balance_history_type_mapping', methods: ['GET'])]
    public function getTypeMapping(): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 创建一个示例BalanceHistory对象以获取默认类型映射
        $balanceHistory = new BalanceHistory();
        $typeDescriptions = $balanceHistory->getTypeDescriptions();
        
        // 转换为前端需要的格式
        $typeOptions = [];
        foreach ($typeDescriptions as $key => $value) {
            $typeOptions[] = [
                'label' => $value,
                'value' => $key
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $typeOptions,
        ]);
    }

    /**
     * 获取主供应商账户（如果是子账号登录，则返回主账号）
     */
    private function getMainSupplier(): Supplier
    {
        $user = $this->getUser();
        
        // 如果是子账号，获取主账号
        if ($user instanceof SupplierSubAccount) {
            return $user->getSupplier();
        }
        
        // 如果是主账号，直接返回
        if ($user instanceof Supplier) {
            return $user;
        }
        
        // 如果都不是，抛出异常
        throw $this->createAccessDeniedException('Invalid user type');
    }
}