<?php

namespace App\Controller\Admin;

use App\Entity\BalanceHistory;
use App\Entity\Supplier;
use App\Entity\Customer;
use App\Service\BalanceHistoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin{adminLoginPath}/balance-history', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
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
    #[Route('/list', name: 'admin_balance_history_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $userType = $request->query->get('userType');
        $username = $request->query->get('username');
        $type = $request->query->get('type');

        $repository = $this->entityManager->getRepository(BalanceHistory::class);
        $queryBuilder = $repository->createQueryBuilder('bh');

        // 添加筛选条件
        if ($userType) {
            $queryBuilder->andWhere('bh.userType = :userType')
                ->setParameter('userType', $userType);
        }

        // 根据用户名筛选 - 修复搜索逻辑问题
        if ($username) {
            // 获取用户ID - 修复逻辑：确保用户名和用户类型匹配
            $userIds = [];
            
            if (!$userType || $userType === 'supplier') {
                $suppliers = $this->entityManager->getRepository(Supplier::class)
                    ->createQueryBuilder('s')
                    ->select('s.id')
                    ->where('s.username = :username')
                    ->setParameter('username', $username)
                    ->getQuery()
                    ->getResult();
                
                foreach ($suppliers as $supplier) {
                    $userIds[] = ['id' => $supplier['id'], 'type' => 'supplier'];
                }
            }
            
            if (!$userType || $userType === 'customer') {
                $customers = $this->entityManager->getRepository(Customer::class)
                    ->createQueryBuilder('c')
                    ->select('c.id')
                    ->where('c.username = :username')
                    ->setParameter('username', $username)
                    ->getQuery()
                    ->getResult();
                
                foreach ($customers as $customer) {
                    $userIds[] = ['id' => $customer['id'], 'type' => 'customer'];
                }
            }
            
            // 构建OR条件确保用户名和用户类型都匹配
            if (!empty($userIds)) {
                $orConditions = [];
                $parameters = [];
                
                foreach ($userIds as $index => $user) {
                    $orConditions[] = '(bh.userId = :userId' . $index . ' AND bh.userType = :userType' . $index . ')';
                    $parameters['userId' . $index] = $user['id'];
                    $parameters['userType' . $index] = $user['type'];
                }
                
                $queryBuilder->andWhere('(' . implode(' OR ', $orConditions) . ')');
                foreach ($parameters as $key => $value) {
                    $queryBuilder->setParameter($key, $value);
                }
            } else {
                // 如果没有找到匹配的用户，返回空结果
                $queryBuilder->andWhere('1=0');
            }
        }

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

        // 获取用户仓库
        $supplierRepository = $this->entityManager->getRepository(Supplier::class);
        $customerRepository = $this->entityManager->getRepository(Customer::class);
        
        // 格式化数据
        $data = [];
        foreach ($balanceHistories as $history) {
            // 获取用户名
            $username = '未知用户';
            if ($history->getUserType() === 'supplier') {
                $supplier = $supplierRepository->find($history->getUserId());
                if ($supplier) {
                    $username = $supplier->getUsername();
                }
            } elseif ($history->getUserType() === 'customer') {
                $customer = $customerRepository->find($history->getUserId());
                if ($customer) {
                    $username = $customer->getUsername();
                }
            }
            
            $data[] = [
                'id' => $history->getId(),
                'userType' => $history->getUserType(),
                'userId' => $history->getUserId(),
                'username' => $username,
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
    #[Route('/user/{userType}/{userId}', name: 'admin_balance_history_user', methods: ['GET'])]
    public function getByUser(string $userType, int $userId): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        if (!in_array($userType, ['supplier', 'customer'])) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid user type',
            ], 400);
        }

        $balanceHistories = $this->balanceHistoryService->getBalanceHistoryByUser($userType, $userId);

        // 获取用户名
        $username = '未知用户';
        if ($userType === 'supplier') {
            $supplier = $this->entityManager->getRepository(Supplier::class)->find($userId);
            if ($supplier) {
                $username = $supplier->getUsername();
            }
        } elseif ($userType === 'customer') {
            $customer = $this->entityManager->getRepository(Customer::class)->find($userId);
            if ($customer) {
                $username = $customer->getUsername();
            }
        }

        // 格式化数据
        $data = [];
        foreach ($balanceHistories as $history) {
            $data[] = [
                'id' => $history->getId(),
                'userType' => $history->getUserType(),
                'userId' => $history->getUserId(),
                'username' => $username,
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
    #[Route('/type/{type}', name: 'admin_balance_history_type', methods: ['GET'])]
    public function getByType(string $type): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $balanceHistories = $this->balanceHistoryService->getBalanceHistoryByType($type);

        // 获取用户仓库
        $supplierRepository = $this->entityManager->getRepository(Supplier::class);
        $customerRepository = $this->entityManager->getRepository(Customer::class);
        
        // 格式化数据
        $data = [];
        foreach ($balanceHistories as $history) {
            // 获取用户名
            $username = '未知用户';
            if ($history->getUserType() === 'supplier') {
                $supplier = $supplierRepository->find($history->getUserId());
                if ($supplier) {
                    $username = $supplier->getUsername();
                }
            } elseif ($history->getUserType() === 'customer') {
                $customer = $customerRepository->find($history->getUserId());
                if ($customer) {
                    $username = $customer->getUsername();
                }
            }
            
            $data[] = [
                'id' => $history->getId(),
                'userType' => $history->getUserType(),
                'userId' => $history->getUserId(),
                'username' => $username,
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
    #[Route('/type-mapping', name: 'admin_balance_history_type_mapping', methods: ['GET'])]
    public function getTypeMapping(): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
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
}