<?php

namespace App\Entity;

use App\Repository\ProductPriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 商品价格表 (ProductPrice)
 * 说明：存储商品在不同区域/业务类型的价格信息
 *
 * =============================================
 * 字段说明
 * =============================================
 * id                    价格ID，主键，自增
 * product_id            商品ID（外键关联Product表）
 * region                区域代码（US,UK,DE,FR,CA等）
 * currency              币种（USD,GBP,EUR,CAD等）
 * business_type         业务类型：dropship-一件代发, wholesale-批发
 * original_price        原价（精确到4位小数）
 * selling_price         售价（精确到4位小数）
 * discount_rate         折扣率（如0.1表示10%折扣，精确到4位小数，前端输入百分比，后端存储小数）
 * is_promotion          是否促销中：0-否, 1-是
 * promotion_start_at    促销开始时间
 * promotion_end_at      促销结束时间
 * member_discount       会员折扣（JSON格式，存储不同会员等级的折扣，前端输入百分比，后端存储小数）
 * min_wholesale_quantity 最小批发起订量
 * is_active             是否启用：0-否, 1-是
 * created_at            创建时间
 * updated_at            最后更新时间
 */
#[ORM\Entity(repositoryClass: ProductPriceRepository::class)]
#[ORM\Table(name: '`product_price`')]
#[ORM\HasLifecycleCallbacks]
class ProductPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * 商品ID（外键关联Product表）
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    /**
     * 区域代码（US,UK,DE,FR,CA等）
     */
    #[ORM\Column(type: 'string', length: 10)]
    private ?string $region = null;

    /**
     * 币种（USD,GBP,EUR,CAD等）
     */
    #[ORM\Column(type: 'string', length: 10)]
    private ?string $currency = null;

    /**
     * 业务类型：dropship-一件代发, wholesale-批发
     */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'dropship'])]
    private string $businessType = 'dropship';

    /**
     * 原价（精确到4位小数）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, nullable: true)]
    private ?string $originalPrice = null;

    /**
     * 售价（精确到4位小数）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    private ?string $sellingPrice = null;

    /**
     * 折扣率（如0.1表示10%折扣，精确到4位小数，前端输入百分比，后端存储小数）
     */
    #[ORM\Column(type: 'decimal', precision: 6, scale: 4, nullable: true)]
    private ?string $discountRate = null;

    /**
     * 是否促销中：0-否, 1-是
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPromotion = false;

    /**
     * 促销开始时间
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $promotionStartAt = null;

    /**
     * 促销结束时间
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $promotionEndAt = null;

    /**
     * 会员折扣（JSON格式，前端输入百分比，后端存储小数）
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $memberDiscount = [];

    /**
     * 最小批发起订量
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $minWholesaleQuantity = null;

    /**
     * 是否启用：0-否, 1-是
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    /**
     * 创建时间
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * 最后更新时间
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getBusinessType(): string
    {
        return $this->businessType;
    }

    public function setBusinessType(string $businessType): self
    {
        $this->businessType = $businessType;
        return $this;
    }

    public function getOriginalPrice(): ?string
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?string $originalPrice): self
    {
        $this->originalPrice = $originalPrice;
        return $this;
    }

    public function getSellingPrice(): ?string
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(string $sellingPrice): self
    {
        $this->sellingPrice = $sellingPrice;
        return $this;
    }

    public function getDiscountRate(): ?string
    {
        return $this->discountRate;
    }

    public function setDiscountRate(?string $discountRate): self
    {
        $this->discountRate = $discountRate;
        return $this;
    }

    public function isPromotion(): bool
    {
        return $this->isPromotion;
    }

    public function setIsPromotion(bool $isPromotion): self
    {
        $this->isPromotion = $isPromotion;
        return $this;
    }

    public function getPromotionStartAt(): ?\DateTimeInterface
    {
        return $this->promotionStartAt;
    }

    public function setPromotionStartAt(?\DateTimeInterface $promotionStartAt): self
    {
        $this->promotionStartAt = $promotionStartAt;
        return $this;
    }

    public function getPromotionEndAt(): ?\DateTimeInterface
    {
        return $this->promotionEndAt;
    }

    public function setPromotionEndAt(?\DateTimeInterface $promotionEndAt): self
    {
        $this->promotionEndAt = $promotionEndAt;
        return $this;
    }

    public function getMemberDiscount(): ?array
    {
        return $this->memberDiscount;
    }

    public function setMemberDiscount(?array $memberDiscount): self
    {
        $this->memberDiscount = $memberDiscount;
        return $this;
    }

    public function getMinWholesaleQuantity(): ?int
    {
        return $this->minWholesaleQuantity;
    }

    public function setMinWholesaleQuantity(?int $minWholesaleQuantity): self
    {
        $this->minWholesaleQuantity = $minWholesaleQuantity;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}