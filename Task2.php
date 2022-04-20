<?php

/**
 *
 */
interface IDiscount
{
    /**
     * @param array $products
     * @return array
     */
    public function calculate(array $products): array;

    /**
     * @return mixed
     */
    public function getName();
}

/**
 *
 */
abstract class Discount implements IDiscount
{
    /**
     * @var string
     */
    protected string $name;
    /**
     * @var float
     */
    protected float $amount;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array $products
     * @return array
     */
    public function calculate(array $products): array
    {
        $result = array();
        $discountValue = $this->amount / $this->countProducts($products);
        if ($this->checkProductsTotal($products)) {
            foreach ($products as $product) {
                $result[] = $discountValue * $product['amount'];
            }
        } else {
            foreach ($products as $product) {
                $result[] = 0.0;
            }
        }
        return $result;
    }

    /**
     * @param array $products
     * @return int|mixed
     */
    protected function countProducts(array $products)
    {
        $count = 0;
        foreach ($products as $product) {
            $count += $product['amount'];
        }
        return $count;
    }

    /**
     * @param $products
     * @return bool
     */
    protected function checkProductsTotal($products): bool
    {
        $totalProductSumm = 0.0;
        foreach ($products as $product) {
            $totalProductSumm += $product['product']->getPrice() * $product['amount'];
        }
        if ($totalProductSumm > $this->amount) {
            return true;
        }
        return false;
    }

}

/**
 *
 */
class FirstPurchaseDiscount extends Discount
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'First purchase discount';
        $this->amount = 100.0;
    }
}

/**
 *
 */
class PromoCodeDiscount extends Discount
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'Promo code discount';
        $this->amount = 500.0;
    }

}

/**
 *
 */
class StudentDiscount extends Discount
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'Student discount 5%';
        $this->amount = 5.0;
    }

    /**
     * @param array $products
     * @return array
     */
    public function calculate(array $products): array
    {
        $result = array();
        foreach ($products as $product) {
            $result[] = (($product['product']->getPrice() / 100) * $this->amount) * $product['amount'];
        }
        return $result;
    }
}

/**
 *
 */
class CheckPrinter
{
    /**
     * @var string
     */
    private string $discountString = "\n Applied discounts: ";
    /**
     * @var string
     */
    private string $totalString = "\n Total = %0.2f \n";
    /**
     * @var string
     */
    private string $checkString = '';
    /**
     * @var float
     */
    private float $totalCheckSum = 0.0;

    /**
     * @param array $cartList
     * @return void
     */
    public function printCheck(array $cartList)
    {
        $cartProducts = $cartList[0];
        $discounts = $cartList[1];
        $discountValues = $cartList[2];
        $resultDiscount = array();
        for ($i = 0; $i < count($cartProducts); $i++) {
            $totalDiscount = 0.0;
            for ($j = 0; $j < count($discounts); $j++) {
                $totalDiscount += $discountValues[$j][$i];
            }
            $resultDiscount[] = $totalDiscount;
        }
        $index = 0;
        foreach ($cartProducts as $product) {
            $totalProductSum = $product['product']->getPrice() * $product['amount'] - $resultDiscount[$index];
            $this->totalCheckSum += $totalProductSum;
            echo sprintf("\n Product - %s qy - %d sum - %0.2f discount - %0.2f total - %0.2f", $product['product']->getName(), $product['amount'], ($product['product']->getPrice() * $product['amount']), $resultDiscount[$index], $totalProductSum);
            $index++;
        }

        foreach ($discounts as $discount) {
            $this->discountString .= ' ' . $discount->getName();
        }
        $this->totalString = sprintf($this->totalString, $this->totalCheckSum);

        echo $this->discountString;
        echo $this->totalString;
    }
}

/**
 *
 */
class Product
{
    /**
     * @var string
     */
    private string $name;
    /**
     * @var float
     */
    private float $price;

    /**
     * @param string $name
     * @param float $price
     */
    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }
}

/**
 *
 */
class Cart
{

    /**
     * @var array
     */
    private array $discounts = [];

    /**
     * @var array
     */
    private array $products = [];


    /**
     * @param Product $product
     * @return void
     */
    public function addToCart(Product $product)
    {
        if (isset($this->products[$product->getName()])) {
            $this->products[$product->getName()] = ['product' => $product, 'amount' => $this->products[$product->getName()]['amount'] + 1];
        } else {
            $this->products[$product->getName()] = ['product' => $product, "amount" => 1];
        }
    }

    /**
     * @param IDiscount $discount
     * @return void
     * @throws Exception
     */
    public function setDiscount(IDiscount $discount)
    {
        $used = false;
        $className = $discount->getName();
        foreach ($this->discounts as $cartDiscount) {
            if ($cartDiscount->getName() == $className) {
                $used = true;
                break;
            }
        }
        if ($used || empty($this->products)) {
            throw new Exception("Discount error");
        } else {
            $this->discounts[] = $discount;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function calculateCheck(): array
    {
        if (!empty($this->products)) {
            $result = null;
            foreach ($this->discounts as $discount) {
                $result[] = $discount->calculate($this->products);
            }
            var_dump($this->products);
            var_dump($result);
            return array($this->products, $this->discounts, $result);
        }
        throw new Exception("Cart is empty");
    }
}


$prod1 = new Product('Product1', 1000.0);
$prod2 = new Product('Product2', 5000.00);
$disc1 = new FirstPurchaseDiscount();
$disc2 = new PromoCodeDiscount();
$disc3 = new StudentDiscount();
$printer = new CheckPrinter();
$cart = new Cart();
$cart->addToCart($prod1);
$cart->addToCart($prod2);

$cart->setDiscount($disc2);
$cart->setDiscount($disc1);
$cart->setDiscount($disc3);

$printer->printCheck($cart->calculateCheck());