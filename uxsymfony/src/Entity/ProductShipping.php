<?php

namespace App\Entity;

use App\Repository\ProductShippingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 商品物流表 (ProductShipping)
 * 说明：存储商品在不同区域的物流配送信息
 *
 * =============================================
 * 字段说明
 * =============================================
 * id                    物流ID，主键，自增
 * product_id            商品ID（外键关联Product表）
 * region                区域代码（US,UK,DE,FR,CA等）
 * shipping_method       物流方式（Standard Shipping, Economy Shipping, Self-Pick up等）
 * shipping_price        运费（精确到4位小数）
 * discounted_price      折后运费（精确到4位小数）
 * currency              币种（USD,GBP,EUR,CAD等）
 * delivery_time         参考时效（天数或天数区间，如"5-7天"）
 * carriers              承运商列表（JSON数组，如["FedEx","USPS","UPS"]）
 * is_default            是否默认物流方式：0-否, 1-是
 * is_active             是否启用：0-否, 1-是
 * created_at            创建时间
 * updated_at            最后更新时间
 */
#[ORM\Entity(repositoryClass: ProductShippingRepository::class)]
#[ORM\Table(name: '`product_shipping`')]
#[ORM\HasLifecycleCallbacks]
class ProductShipping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * 商品ID（外键关联Product表）
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'shippings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    /**
     * 区域代码（US,UK,DE,FR,CA等）
     */
    #[ORM\Column(type: 'string', length: 10)]
    private ?string $region = null;

    /**
     * 物流方式
     */
    #[ORM\Column(type: 'string', length: 100)]
    private ?string $shippingMethod = null;

    /**
     * 运费（精确到4位小数）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    private ?string $shippingPrice = null;

    /**
     * 折后运费（精确到4位小数）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, nullable: true)]
    private ?string $discountedPrice = null;

    /**
     * 币种（USD,GBP,EUR,CAD等）
     */
    #[ORM\Column(type: 'string', length: 10)]
    private ?string $currency = null;

    /**
     * 参考时效
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $deliveryTime = null;

    /**
     * 承运商列表（JSON数组）
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $carriers = [];

    /**
     * 是否默认物流方式：0-否, 1-是
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDefault = false;

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

    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(string $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    public function getShippingPrice(): ?string
    {
        return $this->shippingPrice;
    }

    public function setShippingPrice(string $shippingPrice): self
    {
        $this->shippingPrice = $shippingPrice;
        return $this;
    }

    public function getDiscountedPrice(): ?string
    {
        return $this->discountedPrice;
    }

    public function setDiscountedPrice(?string $discountedPrice): self
    {
        $this->discountedPrice = $discountedPrice;
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

    public function getDeliveryTime(): ?string
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(?string $deliveryTime): self
    {
        $this->deliveryTime = $deliveryTime;
        return $this;
    }

    public function getCarriers(): ?array
    {
        return $this->carriers;
    }

    public function setCarriers(?array $carriers): self
    {
        $this->carriers = $carriers;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
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
