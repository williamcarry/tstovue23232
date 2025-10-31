<?php

namespace App\Entity;

use App\Repository\WithdrawalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 提现申请表 (Withdrawal)
 * 说明：存储供应商的提现申请信息
 *
 * =============================================
 * 字段说明
 * =============================================
 * id                    提现申请ID
 * supplier_id           供应商ID
 * amount                提现金额
 * payment_info          收款信息（银行账户、支付宝等）
 * status                状态：pending-待审核, approved-已通过, rejected-已拒绝
 * remark                备注（审核意见等）
 * reviewed_by           审核人（管理员表用户名）
 * reviewed_at           审核时间
 * created_at            申请时间
 * updated_at            最后更新时间
 * payment_screenshot    打款截图URL
 * balance_history_id    关联的余额历史记录ID
 */
#[ORM\Entity(repositoryClass: WithdrawalRepository::class)]
#[ORM\Table(name: '`withdrawal`')]
class Withdrawal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * 供应商ID
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    private ?int $supplierId = null;

    /**
     * 提现金额
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private ?string $amount = null;

    /**
     * 收款信息（银行账户、支付宝等）
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $paymentInfo = null;

    /**
     * 状态：pending-待审核, approved-已通过, rejected-已拒绝
     * @var string
     */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'pending'])]
    private string $status = 'pending';

    /**
     * 备注（审核意见等）
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null;

    /**
     * 审核人（管理员表用户名）
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    private ?string $reviewedBy = null;

    /**
     * 审核时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $reviewedAt = null;

    /**
     * 申请时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * 最后更新时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * 打款截图URL
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $paymentScreenshot = null;

    /**
     * 关联的余额历史记录ID
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $balanceHistoryId = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = 'pending';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupplierId(): ?int
    {
        return $this->supplierId;
    }

    public function setSupplierId(int $supplierId): self
    {
        $this->supplierId = $supplierId;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getPaymentInfo(): ?string
    {
        return $this->paymentInfo;
    }

    public function setPaymentInfo(?string $paymentInfo): self
    {
        $this->paymentInfo = $paymentInfo;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;
        return $this;
    }

    public function getReviewedBy(): ?string
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?string $reviewedBy): self
    {
        $this->reviewedBy = $reviewedBy;
        return $this;
    }

    public function getReviewedAt(): ?\DateTimeInterface
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeInterface $reviewedAt): self
    {
        $this->reviewedAt = $reviewedAt;
        return $this;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * 获取打款截图URL
     */
    public function getPaymentScreenshot(): ?string
    {
        return $this->paymentScreenshot;
    }

    /**
     * 设置打款截图URL
     */
    public function setPaymentScreenshot(?string $paymentScreenshot): self
    {
        $this->paymentScreenshot = $paymentScreenshot;
        return $this;
    }

    /**
     * 获取关联的余额历史记录ID
     */
    public function getBalanceHistoryId(): ?int
    {
        return $this->balanceHistoryId;
    }

    /**
     * 设置关联的余额历史记录ID
     */
    public function setBalanceHistoryId(?int $balanceHistoryId): self
    {
        $this->balanceHistoryId = $balanceHistoryId;
        return $this;
    }

    /**
     * 获取状态显示文本
     */
    public function getStatusText(): string
    {
        $statusMap = [
            'pending' => '待审核',
            'approved' => '已通过',
            'rejected' => '已拒绝'
        ];
        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * 获取状态标签类型
     */
    public function getStatusTagType(): string
    {
        $typeMap = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];
        return $typeMap[$this->status] ?? 'info';
    }
}