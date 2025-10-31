<?php

namespace App\Entity;

use App\Repository\SupplierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 供应商表 (Supplier)
 * 说明：存储供应商的基本信息和账号信息
 *
 * =============================================
 * 字段说明
 * =============================================
 * -- ==================== 账号信息 ====================
 * id                    供应商ID
 * username              登录用户名
 * password              登录密码（加密存储）
 * email                 联系邮箱（用于登录和通知）
 * roles                 用户角色（如ROLE_SUPPLIER等）
 * 
 * -- ==================== 供应商类型 ====================
 * supplier_type         供应商类型：company-公司, individual-个人
 * 
 * -- ==================== 基本信息 ====================
 * contact_person        联系人姓名
 * contact_phone         联系电话
 * contact_address       联系地址
 * 
 * -- ==================== 公司信息（supplier_type='company'时必填） ====================
 * company_name          公司名称
 * company_type          公司类型：factory-工厂, trader-贸易商, factory_trader-工贸一体, brand-品牌商
 * business_license_number 营业执照注册号
 * business_license_image  营业执照照片URL
 * legal_person_name     法人姓名
 * legal_person_id_card  法人身份证号
 * legal_person_id_front 法人身份证正面照片URL
 * legal_person_id_back  法人身份证反面照片URL
 * registered_capital    注册资本（万元）
 * establishment_date    公司成立日期
 * business_scope        经营范围
 * bank_account_name     银行开户名称
 * bank_account_number   银行账号
 * bank_name            开户银行名称
 * bank_branch          开户支行
 * tax_number           税务登记号/统一社会信用代码
 * 
 * -- ==================== 个人信息（supplier_type='individual'时必填） ====================
 * individual_name       个人真实姓名
 * individual_id_card    个人身份证号
 * individual_id_front   个人身份证正面照片URL
 * individual_id_back    个人身份证反面照片URL
 * individual_bank_account 个人银行账号
 * individual_bank_name  个人开户银行
 * 
 * -- ==================== 业务信息 ====================
 * main_category         主营业务介绍
 * has_cross_border_experience 是否有跨境电商经验：0-否, 1-是
 * annual_sales_volume   年销售额（万元）
 * warehouse_address     仓库地址可选
 * 
 * -- ==================== 审核信息 ====================
 * audit_status          审核状态：pending-待审核, approved-已通过, rejected-已拒绝, resubmit-待重新提交
 * audit_remark          审核备注（拒绝原因等）
 * audited_by           审核人（管理员表用户名）
 * audited_at           审核时间
 * 
 * -- ==================== 账号状态 ====================
 * is_active            账号是否激活：0-禁用, 1-激活
 * is_verified          是否已验证邮箱：0-未验证, 1-已验证
 * 
 * -- ==================== 余额相关字段 ====================
 * balance                余额（用于存储供应商的账户余额，支持抽佣扣费）
 * balance_frozen         冻结余额（用于存储供应商被冻结的金额）
 * 
 * -- ==================== 会员相关字段 ====================
 * membership_type        会员类型：none-无会员, monthly-月会员, quarterly-季度会员, half_yearly-半年会员, yearly-年会员
 * membership_expired_at  会员到期时间（记录会员的到期时间，用于判断是否享有免抽佣特权）
 * 
 * -- ==================== 佣金相关字段 ====================
 * commission_rate        佣金比例（供应商自定义的佣金比例，如0.1表示10%，null表示使用网站统一比例）佣金比例精确到4位小数
 * 
 * -- ==================== 时间戳 ====================
 * created_at           注册时间
 * updated_at           最后更新时间
 * last_login_at        最后登录时间
 */
#[ORM\Entity(repositoryClass: SupplierRepository::class)]
#[ORM\Table(name: '`supplier`')]
class Supplier implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * 登录用户名
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $username = null;

    /**
     * 用户角色（如ROLE_SUPPLIER等）
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * 登录密码（加密存储）
     * @var string|null The hashed password
     */
    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    /**
     * 联系邮箱（用于登录和通知）
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $email = null;

    /**
     * 供应商类型：company-公司, individual-个人
     * @var string
     */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'company'])]
    private string $supplierType = 'company'; // company-公司, individual-个人

    /**
     * 联系人姓名
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100)]
    private ?string $contactPerson = null;

    /**
     * 联系电话
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 20)]
    private ?string $contactPhone = null;

    /**
     * 联系地址
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contactAddress = null;

    /**
     * 公司名称
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $companyName = null;

    /**
     * 公司类型：factory-工厂, trader-贸易商, factory_trader-工贸一体, brand-品牌商
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $companyType = null; // factory, trader, factory_trader, brand

    /**
     * 营业执照注册号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $businessLicenseNumber = null;

    /**
     * 营业执照照片URL
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $businessLicenseImage = null;

    /**
     * 法人姓名
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $legalPersonName = null;

    /**
     * 法人身份证号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 18, nullable: true)]
    private ?string $legalPersonIdCard = null;

    /**
     * 法人身份证正面照片URL
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $legalPersonIdFront = null;

    /**
     * 法人身份证反面照片URL
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $legalPersonIdBack = null;

    /**
     * 注册资本（万元）
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private ?string $registeredCapital = null;

    /**
     * 公司成立日期
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $establishmentDate = null;

    /**
     * 经营范围
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $businessScope = null;

    /**
     * 银行开户名称
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $bankAccountName = null;

    /**
     * 银行账号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $bankAccountNumber = null;

    /**
     * 开户银行名称
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $bankName = null;

    /**
     * 开户支行
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $bankBranch = null;

    /**
     * 税务登记号/统一社会信用代码
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $taxNumber = null;

    /**
     * 个人真实姓名
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $individualName = null;

    /**
     * 个人身份证号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 18, nullable: true)]
    private ?string $individualIdCard = null;

    /**
     * 个人身份证正面照片URL
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $individualIdFront = null;

    /**
     * 个人身份证反面照片URL
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $individualIdBack = null;

    /**
     * 个人银行账号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $individualBankAccount = null;

    /**
     * 个人开户银行
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $individualBankName = null;

    /**
     * 主营业务介绍
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mainCategory = null;

    /**
     * 是否有跨境电商经验：0-否, 1-是
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $hasCrossBorderExperience = false;

    /**
     * 年销售额（万元）
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private ?string $annualSalesVolume = null;

    /**
     * 仓库地址可选
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $warehouseAddress = null;

    /**
     * 审核状态：pending-待审核, approved-已通过, rejected-已拒绝, resubmit-待重新提交
     * @var string
     */
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'pending'])]
    private string $auditStatus = 'pending'; // pending, approved, rejected, resubmit

    /**
     * 审核备注（拒绝原因等）
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $auditRemark = null;

    /**
     * 审核人（管理员表用户名）
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    private ?string $auditedBy = null;

    /**
     * 审核时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $auditedAt = null;

    /**
     * 账号是否激活：0-禁用, 1-激活
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    /**
     * 是否已验证邮箱：0-未验证, 1-已验证
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    /**
     * 余额（用于存储供应商的账户余额，支持抽佣扣费）
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private ?string $balance = null;

    /**
     * 冻结余额（用于存储供应商被冻结的金额）
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private ?string $balanceFrozen = null;

    /**
     * 会员类型：none-无会员, monthly-月会员, quarterly-季度会员, half_yearly-半年会员, yearly-年会员
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $membershipType = null;

    /**
     * 会员到期时间（记录会员的到期时间，用于判断是否享有免抽佣特权）
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $membershipExpiredAt = null;

    /**
     * 佣金比例（供应商自定义的佣金比例，如0.1表示10%，null表示使用网站统一比例）佣金比例精确到4位小数
     * @var string|null
     */
    #[ORM\Column(type: 'decimal', precision: 5, scale: 4, nullable: true)]
    private ?string $commissionRate = null;

    /**
     * 注册时间
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
     * 最后登录时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    // 移除了与SupplierRole的关联

    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: SupplierSubAccount::class, cascade: ['persist', 'remove'])]
    private Collection $supplierSubAccounts;

    public function __construct()
    {
        $this->supplierSubAccounts = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->supplierType = 'company';
        $this->auditStatus = 'pending';
        $this->isActive = true;
        $this->isVerified = false;
        $this->hasCrossBorderExperience = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_SUPPLIER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getCompanyType(): ?string
    {
        return $this->companyType;
    }

    public function setCompanyType(?string $companyType): self
    {
        $this->companyType = $companyType;
        return $this;
    }

    public function getBusinessLicenseNumber(): ?string
    {
        return $this->businessLicenseNumber;
    }

    public function setBusinessLicenseNumber(?string $businessLicenseNumber): self
    {
        $this->businessLicenseNumber = $businessLicenseNumber;
        return $this;
    }

    public function getBusinessLicenseImage(): ?string
    {
        return $this->businessLicenseImage;
    }

    public function setBusinessLicenseImage(?string $businessLicenseImage): self
    {
        $this->businessLicenseImage = $businessLicenseImage;
        return $this;
    }

    public function getLegalPersonName(): ?string
    {
        return $this->legalPersonName;
    }

    public function setLegalPersonName(?string $legalPersonName): self
    {
        $this->legalPersonName = $legalPersonName;
        return $this;
    }

    public function getLegalPersonIdCard(): ?string
    {
        return $this->legalPersonIdCard;
    }

    public function setLegalPersonIdCard(?string $legalPersonIdCard): self
    {
        $this->legalPersonIdCard = $legalPersonIdCard;
        return $this;
    }

    public function getLegalPersonIdFront(): ?string
    {
        return $this->legalPersonIdFront;
    }



    public function setLegalPersonIdFront(?string $legalPersonIdFront): self
    {
        $this->legalPersonIdFront = $legalPersonIdFront;
        return $this;
    }

    public function getLegalPersonIdBack(): ?string
    {
        return $this->legalPersonIdBack;
    }

    public function setLegalPersonIdBack(?string $legalPersonIdBack): self
    {
        $this->legalPersonIdBack = $legalPersonIdBack;
        return $this;
    }

    public function getRegisteredCapital(): ?string
    {
        return $this->registeredCapital;
    }

    public function setRegisteredCapital(?string $registeredCapital): self
    {
        $this->registeredCapital = $registeredCapital;
        return $this;
    }

    public function getEstablishmentDate(): ?\DateTimeInterface
    {
        return $this->establishmentDate;
    }

    public function setEstablishmentDate(?\DateTimeInterface $establishmentDate): self
    {
        $this->establishmentDate = $establishmentDate;
        return $this;
    }

    public function getBusinessScope(): ?string
    {
        return $this->businessScope;
    }

    public function setBusinessScope(?string $businessScope): self
    {
        $this->businessScope = $businessScope;
        return $this;
    }

    public function getBankAccountName(): ?string
    {
        return $this->bankAccountName;
    }

    public function setBankAccountName(?string $bankAccountName): self
    {
        $this->bankAccountName = $bankAccountName;
        return $this;
    }

    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    public function setBankAccountNumber(?string $bankAccountNumber): self
    {
        $this->bankAccountNumber = $bankAccountNumber;
        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): self
    {
        $this->bankName = $bankName;
        return $this;
    }

    public function getBankBranch(): ?string
    {
        return $this->bankBranch;
    }

    public function setBankBranch(?string $bankBranch): self
    {
        $this->bankBranch = $bankBranch;
        return $this;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): self
    {
        $this->taxNumber = $taxNumber;
        return $this;
    }

    // ==================== 个人信息 Getters/Setters ====================
    public function getIndividualName(): ?string
    {
        return $this->individualName;
    }

    public function setIndividualName(?string $individualName): self
    {
        $this->individualName = $individualName;
        return $this;
    }

    public function getIndividualIdCard(): ?string
    {
        return $this->individualIdCard;
    }

    public function setIndividualIdCard(?string $individualIdCard): self
    {
        $this->individualIdCard = $individualIdCard;
        return $this;
    }

    public function getIndividualIdFront(): ?string
    {
        return $this->individualIdFront;
    }

    public function setIndividualIdFront(?string $individualIdFront): self
    {
        $this->individualIdFront = $individualIdFront;
        return $this;
    }

    public function getIndividualIdBack(): ?string
    {
        return $this->individualIdBack;
    }

    public function setIndividualIdBack(?string $individualIdBack): self
    {
        $this->individualIdBack = $individualIdBack;
        return $this;
    }

    public function getIndividualBankAccount(): ?string
    {
        return $this->individualBankAccount;
    }

    public function setIndividualBankAccount(?string $individualBankAccount): self
    {
        $this->individualBankAccount = $individualBankAccount;
        return $this;
    }

    public function getIndividualBankName(): ?string
    {
        return $this->individualBankName;
    }

    public function setIndividualBankName(?string $individualBankName): self
    {
        $this->individualBankName = $individualBankName;
        return $this;
    }

    // ==================== 业务信息 Getters/Setters ====================
    public function getMainCategory(): ?string
    {
        return $this->mainCategory;
    }

    public function setMainCategory(?string $mainCategory): self
    {
        $this->mainCategory = $mainCategory;
        return $this;
    }

    public function getHasCrossBorderExperience(): bool
    {
        return $this->hasCrossBorderExperience;
    }

    public function setHasCrossBorderExperience(bool $hasCrossBorderExperience): self
    {
        $this->hasCrossBorderExperience = $hasCrossBorderExperience;
        return $this;
    }

    public function getAnnualSalesVolume(): ?string
    {
        return $this->annualSalesVolume;
    }

    public function setAnnualSalesVolume(?string $annualSalesVolume): self
    {
        $this->annualSalesVolume = $annualSalesVolume;
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

    // ==================== 审核信息 Getters/Setters ====================
    public function getAuditStatus(): string
    {
        return $this->auditStatus;
    }

    public function setAuditStatus(string $auditStatus): self
    {
        $this->auditStatus = $auditStatus;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->auditStatus === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->auditStatus === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->auditStatus === 'rejected';
    }

    public function needsResubmit(): bool
    {
        return $this->auditStatus === 'resubmit';
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

    // ==================== 账号状态 Getters/Setters ====================

    // ==================== 供应商类型 Getters/Setters ====================
    public function getSupplierType(): string
    {
        return $this->supplierType;
    }

    public function setSupplierType(string $supplierType): self
    {
        $this->supplierType = $supplierType;
        return $this;
    }

    public function isCompany(): bool
    {
        return $this->supplierType === 'company';
    }

    public function isIndividual(): bool
    {
        return $this->supplierType === 'individual';
    }

    // ==================== 基本信息 Getters/Setters ====================
    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(?string $contactPerson): self
    {
        $this->contactPerson = $contactPerson;
        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): self
    {
        $this->contactPhone = $contactPhone;
        return $this;
    }

    public function getContactAddress(): ?string
    {
        return $this->contactAddress;
    }

    public function setContactAddress(?string $contactAddress): self
    {
        $this->contactAddress = $contactAddress;
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

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    // ==================== 余额相关 Getters/Setters ====================
    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    public function getBalanceFrozen(): ?string
    {
        return $this->balanceFrozen;
    }

    public function setBalanceFrozen(?string $balanceFrozen): self
    {
        $this->balanceFrozen = $balanceFrozen;
        return $this;
    }

    // ==================== 会员相关 Getters/Setters ====================
    public function getMembershipType(): ?string
    {
        return $this->membershipType;
    }

    public function setMembershipType(?string $membershipType): self
    {
        $this->membershipType = $membershipType;
        return $this;
    }

    public function getMembershipExpiredAt(): ?\DateTimeInterface
    {
        return $this->membershipExpiredAt;
    }

    public function setMembershipExpiredAt(?\DateTimeInterface $membershipExpiredAt): self
    {
        $this->membershipExpiredAt = $membershipExpiredAt;
        return $this;
    }

    // ==================== 佣金相关 Getters/Setters ====================
    public function getCommissionRate(): ?string
    {
        return $this->commissionRate;
    }

    public function setCommissionRate(?string $commissionRate): self
    {
        $this->commissionRate = $commissionRate;
        return $this;
    }

    /**
     * 检查供应商是否为会员且会员未过期
     */
    public function isMemberActive(): bool
    {
        if ($this->membershipType === null || $this->membershipType === 'none' || $this->membershipExpiredAt === null) {
            return false;
        }
        
        return $this->membershipExpiredAt > new \DateTime();
    }

    /**
     * 检查是否应该使用自定义佣金比例
     */
    public function hasCustomCommissionRate(): bool
    {
        return $this->commissionRate !== null;
    }

    /**
     * 获取实际使用的佣金比例
     * 如果是活跃会员，则返回0（免佣金）
     * 如果有自定义佣金比例且不为0，则返回自定义比例
     * 如果自定义佣金比例为0或未设置，则尝试从网站配置获取默认比例
     * 否则返回null
     * 
     * @param callable|null $siteConfigCallback 获取网站通用佣金比例的回调函数
     * @return string|null 佣金比例（小数形式，如'0.1000'表示10%）
     */
    public function getEffectiveCommissionRate(?callable $siteConfigCallback = null): ?string
    {
        // 如果是活跃会员，免佣金
        if ($this->isMemberActive()) {
            return '0.0000';
        }
        
        // 如果有自定义佣金比例且不为0，使用自定义比例
        if ($this->hasCustomCommissionRate() && bccomp($this->commissionRate, '0', 4) > 0) {
            return $this->commissionRate;
        }
        
        // 如果自定义佣金比例为0或未设置，尝试从网站配置获取默认比例
        if ($siteConfigCallback !== null) {
            $siteCommissionRate = $siteConfigCallback();
            if ($siteCommissionRate !== null) {
                return (string) $siteCommissionRate;
            }
        }
        
        // 如果没有网站通用佣金比例，返回null
        return null;
    }

    /**
     * 获取佣金比例的百分比形式
     * 
     * @param callable|null $siteConfigCallback 获取网站通用佣金比例的回调函数
     * @return float|null 佣金比例（百分比形式，如10表示10%）
     */
    public function getEffectiveCommissionRatePercentage(?callable $siteConfigCallback = null): ?float
    {
        $rate = $this->getEffectiveCommissionRate($siteConfigCallback);
        return $rate !== null ? (float) bcmul($rate, '100', 2) : null;
    }



    // ==================== 时间戳 Getters/Setters ====================

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

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    /**
     * @return Collection<int, SupplierSubAccount>
     */
    public function getSupplierSubAccounts(): Collection
    {
        return $this->supplierSubAccounts;
    }

    public function addSupplierSubAccount(SupplierSubAccount $supplierSubAccount): self
    {
        if (!$this->supplierSubAccounts->contains($supplierSubAccount)) {
            $this->supplierSubAccounts->add($supplierSubAccount);
            $supplierSubAccount->setSupplier($this);
        }

        return $this;
    }

    public function removeSupplierSubAccount(SupplierSubAccount $supplierSubAccount): self
    {
        if ($this->supplierSubAccounts->removeElement($supplierSubAccount)) {
            // set the owning side to null (unless already changed)
            if ($supplierSubAccount->getSupplier() === $this) {
                $supplierSubAccount->setSupplier(null);
            }
        }

        return $this;
    }

    // ==================== 序列化方法 ====================
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'roles' => $this->roles,
            'email' => $this->email,
            'supplierType' => $this->supplierType,
            'contactPerson' => $this->contactPerson,
            'contactPhone' => $this->contactPhone,
            'isActive' => $this->isActive,
            'auditStatus' => $this->auditStatus,
            'balance' => $this->balance,
            'balanceFrozen' => $this->balanceFrozen,
            'membershipType' => $this->membershipType,
            'membershipExpiredAt' => $this->membershipExpiredAt,
            'commissionRate' => $this->commissionRate,
        ];
    }
    
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->roles = $data['roles'];
        $this->email = $data['email'];
        $this->supplierType = $data['supplierType'];
        $this->contactPerson = $data['contactPerson'];
        $this->contactPhone = $data['contactPhone'];
        $this->isActive = $data['isActive'];
        $this->auditStatus = $data['auditStatus'];
        $this->balance = $data['balance'] ?? null;
        $this->balanceFrozen = $data['balanceFrozen'] ?? null;
        $this->membershipType = $data['membershipType'] ?? null;
        $this->membershipExpiredAt = $data['membershipExpiredAt'] ?? null;
        $this->commissionRate = $data['commissionRate'] ?? null;
    }

    // ==================== 业务逻辑方法 ====================
    /**
     * 提交审核
     */
    public function submitForAudit(): self
    {
        $this->auditStatus = 'pending';
        $this->auditRemark = null;
        $this->auditedBy = null;
        $this->auditedAt = null;
        return $this;
    }

    /**
     * 通过审核
     */
    public function approve(int $adminId, ?string $remark = null): self
    {
        $this->auditStatus = 'approved';
        $this->auditRemark = $remark;
        $this->auditedBy = $adminId;
        $this->auditedAt = new \DateTime();
        return $this;
    }

    /**
     * 拒绝审核
     */
    public function reject(int $adminId, string $remark): self
    {
        $this->auditStatus = 'rejected';
        $this->auditRemark = $remark;
        $this->auditedBy = $adminId;
        $this->auditedAt = new \DateTime();
        return $this;
    }

    /**
     * 要求重新提交
     */
    public function requireResubmit(int $adminId, string $remark): self
    {
        $this->auditStatus = 'resubmit';
        $this->auditRemark = $remark;
        $this->auditedBy = $adminId;
        $this->auditedAt = new \DateTime();
        return $this;
    }

    /**
     * 验证公司供应商必填字段
     */
    public function validateCompanyRequiredFields(): array
    {
        $errors = [];
        if ($this->isCompany()) {
            if (empty($this->companyName)) {
                $errors[] = '公司名称不能为空';
            }
            if (empty($this->businessLicenseNumber)) {
                $errors[] = '营业执照注册号不能为空';
            }
            if (empty($this->businessLicenseImage)) {
                $errors[] = '营业执照照片不能为空';
            }
            if (empty($this->legalPersonName)) {
                $errors[] = '法人姓名不能为空';
            }
            if (empty($this->legalPersonIdCard)) {
                $errors[] = '法人身份证号不能为空';
            }
            if (empty($this->legalPersonIdFront)) {
                $errors[] = '法人身份证正面照片不能为空';
            }
            if (empty($this->legalPersonIdBack)) {
                $errors[] = '法人身份证反面照片不能为空';
            }
        }
        return $errors;
    }

    /**
     * 验证个人供应商必填字段
     */
    public function validateIndividualRequiredFields(): array
    {
        $errors = [];
        if ($this->isIndividual()) {
            if (empty($this->individualName)) {
                $errors[] = '个人真实姓名不能为空';
            }
            if (empty($this->individualIdCard)) {
                $errors[] = '个人身份证号不能为空';
            }
            if (empty($this->individualIdFront)) {
                $errors[] = '个人身份证正面照片不能为空';
            }
            if (empty($this->individualIdBack)) {
                $errors[] = '个人身份证反面照片不能为空';
            }
        }
        return $errors;
    }

    /**
     * 获取显示名称（公司名称或个人姓名）
     */
    public function getDisplayName(): string
    {
        if ($this->isCompany()) {
            return $this->companyName ?? $this->username;
        }
        return $this->individualName ?? $this->username;
    }

    public function hasCrossBorderExperience(): ?bool
    {
        return $this->hasCrossBorderExperience;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }
}