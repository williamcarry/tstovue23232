<?php

namespace App\Entity;

use App\Repository\BalanceHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 余额变化记录表 (BalanceHistory)
 * 说明：记录供应商(Supplier)和普通用户(Customer)的余额变化历史
 *
 * =============================================
 * 字段说明
 * =============================================
 * -- ==================== 基本信息 ====================
 * id                    记录ID，主键，自增
 * user_type             用户类型：supplier-供应商, customer-普通用户
 * user_id               用户ID（关联supplier表或customer表的id字段）
 * 
 * -- ==================== 余额变化信息 ====================
 * balance_before        变化前余额
 * balance_after         变化后余额
 * amount                变化金额（正数表示增加，负数表示减少）
 * frozen_balance_before 变化前冻结余额
 * frozen_balance_after  变化后冻结余额
 * frozen_amount         冻结余额变化金额（正数表示冻结增加，负数表示解冻）
 * 
 * -- ==================== 业务信息 ====================
 * type                  业务类型：recharge-充值, withdraw-提现, order-订单, commission-佣金, refund-退款, system-系统调整等
 * description           变化描述
 * reference_id          关联业务ID（如订单ID、提现ID等）
 * 
 * -- ==================== 时间戳 ====================
 * created_at            记录创建时间
 */
#[ORM\Entity(repositoryClass: BalanceHistoryRepository::class)]
#[ORM\Table(name: '`balance_history`')]
#[ORM\Index(columns: ['user_type', 'user_id'], name: 'idx_user')]
#[ORM\Index(columns: ['type'], name: 'idx_type')]
#[ORM\Index(columns: ['created_at'], name: 'idx_created_at')]
class BalanceHistory
{
    /**
     * 记录ID，主键，自增
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * 用户类型：supplier-供应商, customer-普通用户
     * @var string
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $userType;

    /**
     * 用户ID（关联supplier表或customer表的id字段）
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private int $userId;

    /**
     * 变化前余额
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $balanceBefore;

    /**
     * 变化后余额
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $balanceAfter;

    /**
     * 变化金额（正数表示增加，负数表示减少）
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $amount;

    /**
     * 变化前冻结余额
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $frozenBalanceBefore;

    /**
     * 变化后冻结余额
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $frozenBalanceAfter;

    /**
     * 冻结余额变化金额（正数表示冻结增加，负数表示解冻）
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $frozenAmount;

    /**
     * 业务类型：recharge-充值, withdraw-提现, order-订单, commission-佣金, refund-退款, system-系统调整等
     * @var string
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $type;

    /**
     * 变化描述
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $description = null;

    /**
     * 关联业务ID（如订单ID、提现ID等）
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $referenceId = null;

    /**
     * 类型说明（存储不同类型余额变化的说明）
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private array $typeDescriptions = [];

    /**
     * 记录创建时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->balanceBefore = '0.00';
        $this->balanceAfter = '0.00';
        $this->amount = '0.00';
        $this->frozenBalanceBefore = '0.00';
        $this->frozenBalanceAfter = '0.00';
        $this->frozenAmount = '0.00';
        $this->typeDescriptions = [
            'recharge' => '充值',
            'withdraw' => '提现',
            'vip_purchase' => '购买VIP',
            'admin_add' => '后台增加余额',
            'admin_deduct' => '后台减少余额',
            'commission' => '网站佣金',
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): self
    {
        $this->userType = $userType;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getBalanceBefore(): string
    {
        return $this->balanceBefore;
    }

    public function setBalanceBefore(string $balanceBefore): self
    {
        $this->balanceBefore = $balanceBefore;
        return $this;
    }

    public function getBalanceAfter(): string
    {
        return $this->balanceAfter;
    }

    public function setBalanceAfter(string $balanceAfter): self
    {
        $this->balanceAfter = $balanceAfter;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getFrozenBalanceBefore(): string
    {
        return $this->frozenBalanceBefore;
    }

    public function setFrozenBalanceBefore(string $frozenBalanceBefore): self
    {
        $this->frozenBalanceBefore = $frozenBalanceBefore;
        return $this;
    }

    public function getFrozenBalanceAfter(): string
    {
        return $this->frozenBalanceAfter;
    }

    public function setFrozenBalanceAfter(string $frozenBalanceAfter): self
    {
        $this->frozenBalanceAfter = $frozenBalanceAfter;
        return $this;
    }

    public function getFrozenAmount(): string
    {
        return $this->frozenAmount;
    }

    public function setFrozenAmount(string $frozenAmount): self
    {
        $this->frozenAmount = $frozenAmount;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function setReferenceId(?string $referenceId): self
    {
        $this->referenceId = $referenceId;
        return $this;
    }

    public function getTypeDescriptions(): array
    {
        return $this->typeDescriptions;
    }

    public function setTypeDescriptions(array $typeDescriptions): self
    {
        $this->typeDescriptions = $typeDescriptions;
        return $this;
    }

    public function addTypeDescription(string $type, string $description): self
    {
        $this->typeDescriptions[$type] = $description;
        return $this;
    }

    public function getTypeDescription(string $type): ?string
    {
        return $this->typeDescriptions[$type] ?? null;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}