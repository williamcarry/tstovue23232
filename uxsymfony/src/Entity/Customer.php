<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use App\common\VipLevel;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 用户表 (Customer)
 * 说明：存储用户的基本信息、账号信息和VIP等级信息
 *
 * =============================================
 * 字段说明
 * =============================================
 * -- ==================== 账号信息 ====================
 * id                    用户ID，主键，自增
 * username              登录用户名，唯一，必须是英文字母、数字和下划线的组合，且不能以数字或下划线开头
 * password              登录密码（加密存储）
 * email                 联系邮箱（用于登录和通知），唯一
 * mobile                手机号码，唯一
 * is_active             账号状态：false-禁用, true-激活
 * 
 * -- ==================== 基本信息 ====================
 * nickname              用户昵称
 * real_name             真实姓名
 * avatar                用户头像URL
 * gender                性别：0-未知, 1-男, 2-女
 * birthday              生日
 * individual_id_card    个人身份证号
 * individual_id_front   个人身份证正面照片URL
 * individual_id_back    个人身份证反面照片URL
 * is_verified           是否已经实名：false-未实名, true-已实名
 * bank_name             开户银行名称
 * bank_account          银行账号
 * alipay_account        支付宝账号
 * wechat_account        微信账号
 * parent_id             上级用户ID，用于记录推荐关系，默认为0表示无上级
 * 
 * -- ==================== 地址信息 ====================
 * province              省份
 * city                  城市
 * district              区县
 * address               详细地址
 * 
 * -- ==================== VIP等级信息 ====================
 * vip_level             VIP等级：0-普通用户, 1-VIP1, 2-VIP2, 3-VIP3, 4-VIP4, 5-VIP5, 6-VIP6
 * vip_points            VIP积分
 * vip_expire_date       VIP过期时间
 * vip_growth_value      VIP成长值
 * 
 * -- ==================== 账户信息 ====================
 * balance               账户余额（元）
 * frozen_balance        冻结余额（元）
 * consumption_amount    累计消费金额（元）
 * 
 * -- ==================== 统计信息 ====================
 * login_count           登录次数
 * last_login_at         最后登录时间
 * last_login_ip         最后登录IP
 * register_ip           注册IP
 * 
 * -- ==================== 时间戳 ====================
 * created_at            注册时间
 * updated_at            最后更新时间
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: '`customer`')]
class Customer implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * 用户ID，主键，自增
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * 登录用户名，唯一，必须是英文字母、数字和下划线的组合，且不能以数字或下划线开头
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $username = null;

    /**
     * 登录密码（加密存储）
     * @var string|null The hashed password
     */
    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    /**
     * 联系邮箱（用于登录和通知），唯一
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $email = null;

    /**
     * 手机号码，唯一
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 20, unique: true, nullable: true)]
    private ?string $mobile = null;

    /**
     * 账号状态：false-禁用, true-激活
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    /**
     * 用户昵称
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $nickname = null;

    /**
     * 真实姓名
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $realName = null;

    /**
     * 用户头像URL
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $avatar = null;

    /**
     * 性别：0-未知, 1-男, 2-女
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $gender = 0;

    /**
     * 生日
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthday = null;

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
     * 是否已经实名：false-未实名, true-已实名
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    /**
     * 开户银行名称
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $bankName = null;

    /**
     * 银行账号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $bankAccount = null;

    /**
     * 支付宝账号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $alipayAccount = null;

    /**
     * 微信账号
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $wechatAccount = null;

    /**
     * 上级用户ID，用于记录推荐关系，默认为0表示无上级
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $parentId = 0;

    /**
     * 省份
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $province = null;

    /**
     * 城市
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $city = null;

    /**
     * 区县
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $district = null;

    /**
     * 详细地址
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address = null;

    /**
     * VIP等级：0-普通用户, 1-VIP1, 2-VIP2, 3-VIP3, 4-VIP4, 5-VIP5, 6-VIP6
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $vipLevel = 0;

    /**
     * VIP积分
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $vipPoints = 0;

    /**
     * VIP过期时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $vipExpireDate = null;

    /**
     * VIP成长值
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $vipGrowthValue = 0;

    /**
     * 账户余额（元）
     * @var float
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => 0])]
    private float $balance = 0;

    /**
     * 冻结余额（元）
     * @var float
     */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => 0])]
    private float $frozenBalance = 0;

    /**
     * 累计消费金额（元）
     * @var float
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, options: ['default' => 0])]
    private float $consumptionAmount = 0;

    /**
     * 登录次数
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $loginCount = 0;

    /**
     * 最后登录时间
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    /**
     * 最后登录IP
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $lastLoginIp = null;

    /**
     * 注册IP
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $registerIp = null;

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

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
        // 每个用户至少有 ROLE_USER 角色
        return ['ROLE_USER'];
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
        // 如果在用户上存储任何临时的、敏感的数据，在这里清除它
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

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

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

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(?string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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

    public function getBankAccount(): ?string
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?string $bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function getAlipayAccount(): ?string
    {
        return $this->alipayAccount;
    }

    public function setAlipayAccount(?string $alipayAccount): self
    {
        $this->alipayAccount = $alipayAccount;

        return $this;
    }

    public function getWechatAccount(): ?string
    {
        return $this->wechatAccount;
    }

    public function setWechatAccount(?string $wechatAccount): self
    {
        $this->wechatAccount = $wechatAccount;

        return $this;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getVipLevel(): int
    {
        return $this->vipLevel;
    }

    public function setVipLevel(int $vipLevel): self
    {
        $this->vipLevel = $vipLevel;

        return $this;
    }

    public function getVipPoints(): int
    {
        return $this->vipPoints;
    }

    public function setVipPoints(int $vipPoints): self
    {
        $this->vipPoints = $vipPoints;

        return $this;
    }

    public function getVipExpireDate(): ?\DateTimeInterface
    {
        return $this->vipExpireDate;
    }

    public function setVipExpireDate(?\DateTimeInterface $vipExpireDate): self
    {
        $this->vipExpireDate = $vipExpireDate;

        return $this;
    }

    public function getVipGrowthValue(): int
    {
        return $this->vipGrowthValue;
    }

    public function setVipGrowthValue(int $vipGrowthValue): self
    {
        $this->vipGrowthValue = $vipGrowthValue;

        return $this;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getFrozenBalance(): float
    {
        return $this->frozenBalance;
    }

    public function setFrozenBalance(float $frozenBalance): self
    {
        $this->frozenBalance = $frozenBalance;

        return $this;
    }

    public function getConsumptionAmount(): float
    {
        return $this->consumptionAmount;
    }

    public function setConsumptionAmount(float $consumptionAmount): self
    {
        $this->consumptionAmount = $consumptionAmount;

        return $this;
    }

    public function getLoginCount(): int
    {
        return $this->loginCount;
    }

    public function setLoginCount(int $loginCount): self
    {
        $this->loginCount = $loginCount;

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

    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function setLastLoginIp(?string $lastLoginIp): self
    {
        $this->lastLoginIp = $lastLoginIp;

        return $this;
    }

    public function getRegisterIp(): ?string
    {
        return $this->registerIp;
    }

    public function setRegisterIp(?string $registerIp): self
    {
        $this->registerIp = $registerIp;

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
     * 检查用户是否为VIP
     * 
     * @return bool 是否为VIP用户
     */
    public function isVip(): bool
    {
        return VipLevel::isVip($this->vipLevel);
    }

    /**
     * 获取VIP等级名称
     * 
     * @return string VIP等级名称
     */
    public function getVipLevelName(): string
    {
        return VipLevel::getLevelName($this->vipLevel);
    }

    /**
     * 获取VIP等级颜色
     * 
     * @return string VIP等级颜色代码
     */
    public function getVipLevelColor(): string
    {
        return VipLevel::getLevelColor($this->vipLevel);
    }

    /**
     * 检查VIP是否过期
     * 
     * @return bool VIP是否过期
     */
    public function isVipExpired(): bool
    {
        if (!$this->vipExpireDate) {
            return true;
        }
        
        return new \DateTime() > $this->vipExpireDate;
    }

    /**
     * 升级VIP等级
     * 
     * @param int $newLevel 新的VIP等级
     * @param int $months 有效期月数
     * @return self
     */
    public function upgradeVipLevel(int $newLevel, int $months = 12): self
    {
        $this->vipLevel = $newLevel;
        
        // 设置过期时间
        $expireDate = new \DateTime();
        $expireDate->modify("+{$months} months");
        $this->vipExpireDate = $expireDate;
        
        return $this;
    }

    /**
     * 增加VIP积分
     * 
     * @param int $points 要增加的积分
     * @return self
     */
    public function addVipPoints(int $points): self
    {
        $this->vipPoints += $points;
        return $this;
    }

    /**
     * 增加VIP成长值
     * 
     * @param int $growthValue 要增加的成长值
     * @return self
     */
    public function addVipGrowthValue(int $growthValue): self
    {
        $this->vipGrowthValue += $growthValue;
        return $this;
    }

    /**
     * 增加账户余额
     * 
     * @param float $amount 要增加的金额
     * @return self
     */
    public function addBalance(float $amount): self
    {
        $this->balance += $amount;
        return $this;
    }

    /**
     * 减少账户余额
     * 
     * @param float $amount 要减少的金额
     * @return self
     * @throws \Exception 当余额不足时抛出异常
     */
    public function deductBalance(float $amount): self
    {
        if ($this->balance < $amount) {
            throw new \Exception('账户余额不足');
        }
        
        $this->balance -= $amount;
        return $this;
    }

    /**
     * 增加累计消费金额
     * 
     * @param float $amount 要增加的消费金额
     * @return self
     */
    public function addConsumptionAmount(float $amount): self
    {
        $this->consumptionAmount += $amount;
        return $this;
    }

    /**
     * 增加登录次数
     * 
     * @return self
     */
    public function incrementLoginCount(): self
    {
        $this->loginCount++;
        return $this;
    }

    /**
     * 更新最后登录信息
     * 
     * @param string|null $ip 登录IP地址
     * @return self
     */
    public function updateLastLogin(?string $ip = null): self
    {
        $this->lastLoginAt = new \DateTime();
        if ($ip) {
            $this->lastLoginIp = $ip;
        }
        $this->incrementLoginCount();
        return $this;
    }
}