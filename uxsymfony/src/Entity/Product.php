<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 商品表 (Product)
 * 说明：存储商品的基本信息
 *
 * =============================================
 * 字段说明
 * =============================================
 * -- ==================== 基本标识 ====================
 * id                    商品ID，主键，自增
 * sku                   SKU编码（唯一，商品唯一标识）
 * spu                   SPU编码（同一款商品的不同规格共享SPU）
 * supplier_id           供应商ID（外键关联Supplier表）
 * 
 * -- ==================== 基本信息 ====================
 * title                 商品标题（中文）
 * title_en              商品英文标题
 * main_images           主图URL数组（存储七牛云key数组）
 * status                商品状态：draft-草稿, pending-待审核, approved-已上架, rejected-已拒绝, offline-已下架
 * 
 * -- ==================== 分类信息 ====================
 * category_id           一级分类ID（外键关联MenuCategory表）
 * subcategory_id        二级分类ID（外键关联MenuSubcategory表）
 * item_id               三级分类ID（外键关联MenuItem表）
 * brand                 品牌名称
 * 
 * -- ==================== 尺寸重量 ====================
 * length                长度（cm）
 * width                 宽度（cm）
 * height                高度（cm）
 * weight                重量（g）
 * package_length        包装长度（cm）
 * package_width         包装宽度（cm）
 * package_height        包装高度（cm）
 * package_weight        包装重量（g）
 * 
 * -- ==================== 业务类型 ====================
 * support_dropship      是否支持一件代发：0-不支持, 1-支持
 * support_wholesale     是否支持批发：0-不支持, 1-支持
 * support_circle_buy    是否支持圈货：0-不支持, 1-支持
 * support_self_pickup   是否支持自提：0-不支持, 1-支持
 * support_borrowing_address 是否支持借远地址：0-不支持, 1-支持
 * 
 * -- ==================== 库存警戒 ====================
 * alert_stock_line      库存警戒线（当库存低于此值时预警）
 * 
 * -- ==================== 富文本与附件 ====================
 * rich_content          商品详情富文本（HTML）
 * attachments           附件URL列表（JSON格式）
 * 
 * -- ==================== 销售统计 ====================
 * sales_count           销售数量
 * download_count        下载次数
 * view_count            浏览次数
 * 
 * -- ==================== 标签与特性 ====================
 * tags                  商品标签（JSON数组，如["热卖","新品"]）
 * is_featured           是否特别关注：0-否, 1-是
 * is_new                是否新品：0-否, 1-是
 * is_hot                是否热卖：0-否, 1-是
 * is_promotion          是否促销：0-否, 1-是
 * 
 * -- ==================== 审核信息 ====================
 * audit_remark          审核备注
 * audited_by            审核人（管理员用户名）
 * audited_at            审核时间
 * 
 * -- ==================== 时间戳 ====================
 * publish_date          首次上架时间
 * created_at            创建时间
 * updated_at            最后更新时间
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: '`product`')]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * SKU编码（唯一，商品唯一标识）
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $sku = null;

    /**
     * SPU编码（同一款商品的不同规格共享SPU）
     */
    #[ORM\Column(type: 'string', length: 100)]
    private ?string $spu = null;

    /**
     * 供应商ID（外键关联Supplier表）
     */
    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    /**
     * 商品标题（中文）
     */
    #[ORM\Column(type: 'string', length: 500)]
    private ?string $title = null;

    /**
     * 商品英文标题
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $titleEn = null;

    /**
     * 主图URL数组（存储七牛云key数组）
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $mainImages = [];

    /**
     * 商品状态：draft-草稿, pending-待审核, approved-已上架, rejected-已拒绝, offline-已下架
     */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'draft'])]
    private string $status = 'draft';

    /**
     * 一级分类ID（外键关联MenuCategory表）
     */
    #[ORM\ManyToOne(targetEntity: MenuCategory::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?MenuCategory $category = null;

    /**
     * 二级分类ID（外键关联MenuSubcategory表）
     */
    #[ORM\ManyToOne(targetEntity: MenuSubcategory::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?MenuSubcategory $subcategory = null;

    /**
     * 三级分类ID（外键关联MenuItem表）
     */
    #[ORM\ManyToOne(targetEntity: MenuItem::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?MenuItem $item = null;

    /**
     * 品牌名称
     */
    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $brand = null;

    /**
     * 长度（cm）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $length = null;

    /**
     * 宽度（cm）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $width = null;

    /**
     * 高度（cm）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $height = null;

    /**
     * 重量（g）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $weight = null;

    /**
     * 包装长度（cm）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $packageLength = null;

    /**
     * 包装宽度（cm）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $packageWidth = null;

    /**
     * 包装高度（cm）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $packageHeight = null;

    /**
     * 包装重量（g）
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $packageWeight = null;

    /**
     * 是否支持一件代发：0-不支持, 1-支持
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $supportDropship = true;

    /**
     * 是否支持批发：0-不支持, 1-支持
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $supportWholesale = false;

    /**
     * 是否支持圈货：0-不支持, 1-支持
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $supportCircleBuy = false;

    /**
     * 是否支持自提：0-不支持, 1-支持
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $supportSelfPickup = false;

    /**
     * 是否支持借远地址：0-不支持, 1-支持
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $supportBorrowingAddress = false;

    /**
     * 库存警戒线（当库存低于此值时预警）
     */
    #[ORM\Column(type: 'integer', options: ['default' => 10])]
    private int $alertStockLine = 10;

    /**
     * 商品详情富文本（HTML）
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $richContent = null;

    /**
     * 附件URL列表（JSON格式）
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $attachments = [];

    /**
     * 销售数量
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $salesCount = 0;

    /**
     * 下载次数
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $downloadCount = 0;

    /**
     * 浏览次数
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $viewCount = 0;

    /**
     * 商品标签（JSON数组）
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tags = [];

    /**
     * 是否特别关注：0-否, 1-是
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isFeatured = false;

    /**
     * 是否新品：0-否, 1-是
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isNew = false;

    /**
     * 是否热卖：0-否, 1-是
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isHot = false;

    /**
     * 是否促销：0-否, 1-是
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPromotion = false;

    /**
     * 审核备注
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $auditRemark = null;

    /**
     * 审核人（管理员用户名）
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $auditedBy = null;

    /**
     * 审核时间
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $auditedAt = null;

    /**
     * 首次上架时间
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $publishDate = null;

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



    /**
     * 商品库存集合
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductStock::class, cascade: ['persist', 'remove'])]
    private Collection $stocks;

    /**
     * 商品价格集合
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductPrice::class, cascade: ['persist', 'remove'])]
    private Collection $prices;

    /**
     * 商品物流集合
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductShipping::class, cascade: ['persist', 'remove'])]
    private Collection $shippings;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->shippings = new ArrayCollection();
    }

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

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;
        return $this;
    }

    public function getSpu(): ?string
    {
        return $this->spu;
    }

    public function setSpu(string $spu): self
    {
        $this->spu = $spu;
        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): self
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitleEn(?string $titleEn): self
    {
        $this->titleEn = $titleEn;
        return $this;
    }

    public function getMainImages(): ?array
    {
        return $this->mainImages;
    }

    public function setMainImages(?array $mainImages): self
    {
        $this->mainImages = $mainImages;
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

    public function getCategory(): ?MenuCategory
    {
        return $this->category;
    }

    public function setCategory(?MenuCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getSubcategory(): ?MenuSubcategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?MenuSubcategory $subcategory): self
    {
        $this->subcategory = $subcategory;
        return $this;
    }

    public function getItem(): ?MenuItem
    {
        return $this->item;
    }

    public function setItem(?MenuItem $item): self
    {
        $this->item = $item;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getLength(): ?string
    {
        return $this->length;
    }

    public function setLength(?string $length): self
    {
        $this->length = $length;
        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getPackageLength(): ?string
    {
        return $this->packageLength;
    }

    public function setPackageLength(?string $packageLength): self
    {
        $this->packageLength = $packageLength;
        return $this;
    }

    public function getPackageWidth(): ?string
    {
        return $this->packageWidth;
    }

    public function setPackageWidth(?string $packageWidth): self
    {
        $this->packageWidth = $packageWidth;
        return $this;
    }

    public function getPackageHeight(): ?string
    {
        return $this->packageHeight;
    }

    public function setPackageHeight(?string $packageHeight): self
    {
        $this->packageHeight = $packageHeight;
        return $this;
    }

    public function getPackageWeight(): ?string
    {
        return $this->packageWeight;
    }

    public function setPackageWeight(?string $packageWeight): self
    {
        $this->packageWeight = $packageWeight;
        return $this;
    }

    public function isSupportDropship(): bool
    {
        return $this->supportDropship;
    }

    public function setSupportDropship(bool $supportDropship): self
    {
        $this->supportDropship = $supportDropship;
        return $this;
    }

    public function isSupportWholesale(): bool
    {
        return $this->supportWholesale;
    }

    public function setSupportWholesale(bool $supportWholesale): self
    {
        $this->supportWholesale = $supportWholesale;
        return $this;
    }

    public function isSupportCircleBuy(): bool
    {
        return $this->supportCircleBuy;
    }

    public function setSupportCircleBuy(bool $supportCircleBuy): self
    {
        $this->supportCircleBuy = $supportCircleBuy;
        return $this;
    }

    public function isSupportSelfPickup(): bool
    {
        return $this->supportSelfPickup;
    }

    public function setSupportSelfPickup(bool $supportSelfPickup): self
    {
        $this->supportSelfPickup = $supportSelfPickup;
        return $this;
    }

    public function isSupportBorrowingAddress(): bool
    {
        return $this->supportBorrowingAddress;
    }

    public function setSupportBorrowingAddress(bool $supportBorrowingAddress): self
    {
        $this->supportBorrowingAddress = $supportBorrowingAddress;
        return $this;
    }

    public function getAlertStockLine(): int
    {
        return $this->alertStockLine;
    }

    public function setAlertStockLine(int $alertStockLine): self
    {
        $this->alertStockLine = $alertStockLine;
        return $this;
    }

    public function getRichContent(): ?string
    {
        return $this->richContent;
    }

    public function setRichContent(?string $richContent): self
    {
        $this->richContent = $richContent;
        return $this;
    }

    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function setAttachments(?array $attachments): self
    {
        $this->attachments = $attachments;
        return $this;
    }

    public function getSalesCount(): int
    {
        return $this->salesCount;
    }

    public function setSalesCount(int $salesCount): self
    {
        $this->salesCount = $salesCount;
        return $this;
    }

    public function getDownloadCount(): int
    {
        return $this->downloadCount;
    }

    public function setDownloadCount(int $downloadCount): self
    {
        $this->downloadCount = $downloadCount;
        return $this;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): self
    {
        $this->viewCount = $viewCount;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function isHot(): bool
    {
        return $this->isHot;
    }

    public function setIsHot(bool $isHot): self
    {
        $this->isHot = $isHot;
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

    public function getAuditRemark(): ?string
    {
        return $this->auditRemark;
    }

    public function setAuditRemark(?string $auditRemark): self
    {
        $this->auditRemark = $auditRemark;
        return $this;
    }

    public function getAuditedBy(): ?string
    {
        return $this->auditedBy;
    }

    public function setAuditedBy(?string $auditedBy): self
    {
        $this->auditedBy = $auditedBy;
        return $this;
    }

    public function getAuditedAt(): ?\DateTimeInterface
    {
        return $this->auditedAt;
    }

    public function setAuditedAt(?\DateTimeInterface $auditedAt): self
    {
        $this->auditedAt = $auditedAt;
        return $this;
    }

    public function getPublishDate(): ?\DateTimeInterface
    {
        return $this->publishDate;
    }

    public function setPublishDate(?\DateTimeInterface $publishDate): self
    {
        $this->publishDate = $publishDate;
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



    /**
     * @return Collection<int, ProductStock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(ProductStock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks[] = $stock;
            $stock->setProduct($this);
        }

        return $this;
    }

    public function removeStock(ProductStock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            if ($stock->getProduct() === $this) {
                $stock->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductPrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(ProductPrice $price): self
    {
        if (!$this->prices->contains($price)) {
            $this->prices[] = $price;
            $price->setProduct($this);
        }

        return $this;
    }

    public function removePrice(ProductPrice $price): self
    {
        if ($this->prices->removeElement($price)) {
            if ($price->getProduct() === $this) {
                $price->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductShipping>
     */
    public function getShippings(): Collection
    {
        return $this->shippings;
    }

    public function addShipping(ProductShipping $shipping): self
    {
        if (!$this->shippings->contains($shipping)) {
            $this->shippings[] = $shipping;
            $shipping->setProduct($this);
        }

        return $this;
    }

    public function removeShipping(ProductShipping $shipping): self
    {
        if ($this->shippings->removeElement($shipping)) {
            if ($shipping->getProduct() === $this) {
                $shipping->setProduct(null);
            }
        }

        return $this;
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
