<?php

namespace App\Entity;

use App\Repository\ProductStockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 商品库存表 (ProductStock)
 * 说明：存储商品在不同区域/仓库的库存信息
 *
 * =============================================
 * 字段说明
 * =============================================
 * id                    库存ID，主键，自增
 * product_id            商品ID（外键关联Product表）
 * region                区域代码（US,UK,DE,FR,CA等）
 * warehouse_code        仓库代码
 * warehouse_name        仓库名称
 * warehouse_address     仓库地址
 * available_stock       可售库存数量
 * frozen_stock          冻结库存数量
 * total_stock           总库存（available_stock + frozen_stock）
 * stock_status          库存状态：in_stock-有货, low_stock-低库存, out_of_stock-缺货
 * updated_at            最后更新时间
 */
#[ORM\Entity(repositoryClass: ProductStockRepository::class)]
#[ORM\Table(name: '`product_stock`')]
#[ORM\HasLifecycleCallbacks]
class ProductStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * 商品ID（外键关联Product表）
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    /**
     * 区域代码（US,UK,DE,FR,CA等）
     */
    #[ORM\Column(type: 'string', length: 10)]
    private ?string $region = null;

    /**
     * 仓库代码
     */
    #[ORM\Column(type: 'string', length: 50)]
    private ?string $warehouseCode = null;

    /**
     * 仓库名称
     */
    #[ORM\Column(type: 'string', length: 200)]
    private ?string $warehouseName = null;

    /**
     * 仓库地址
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $warehouseAddress = null;

    /**
     * 可售库存数量
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $availableStock = 0;

    /**
     * 冻结库存数量
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $frozenStock = 0;

    /**
     * 总库存（available_stock + frozen_stock）
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalStock = 0;

    /**
     * 库存状态：in_stock-有货, low_stock-低库存, out_of_stock-缺货
     */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'out_of_stock'])]
    private string $stockStatus = 'out_of_stock';

    /**
     * 最后更新时间
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
        $this->calculateTotalStock();
        $this->updateStockStatus();
    }

    /**
     * 计算总库存
     */
    private function calculateTotalStock(): void
    {
        $this->totalStock = $this->availableStock + $this->frozenStock;
    }

    /**
     * 更新库存状态
     */
    private function updateStockStatus(): void
    {
        if ($this->availableStock <= 0) {
            $this->stockStatus = 'out_of_stock';
        } elseif ($this->product && $this->availableStock <= $this->product->getAlertStockLine()) {
            $this->stockStatus = 'low_stock';
        } else {
            $this->stockStatus = 'in_stock';
        }
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

    public function getWarehouseCode(): ?string
    {
        return $this->warehouseCode;
    }

    public function setWarehouseCode(string $warehouseCode): self
    {
        $this->warehouseCode = $warehouseCode;
        return $this;
    }

    public function getWarehouseName(): ?string
    {
        return $this->warehouseName;
    }

    public function setWarehouseName(string $warehouseName): self
    {
        $this->warehouseName = $warehouseName;
        return $this;
    }

    public function getWarehouseAddress(): ?string
    {
        return $this->warehouseAddress;
    }

    public function setWarehouseAddress(?string $warehouseAddress): self
    {
        $this->warehouseAddress = $warehouseAddress;
        return $this;
    }

    public function getAvailableStock(): int
    {
        return $this->availableStock;
    }

    public function setAvailableStock(int $availableStock): self
    {
        $this->availableStock = $availableStock;
        return $this;
    }

    public function getFrozenStock(): int
    {
        return $this->frozenStock;
    }

    public function setFrozenStock(int $frozenStock): self
    {
        $this->frozenStock = $frozenStock;
        return $this;
    }

    public function getTotalStock(): int
    {
        return $this->totalStock;
    }

    public function getStockStatus(): string
    {
        return $this->stockStatus;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setTotalStock(int $totalStock): static
    {
        $this->totalStock = $totalStock;

        return $this;
    }

    public function setStockStatus(string $stockStatus): static
    {
        $this->stockStatus = $stockStatus;

        return $this;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
