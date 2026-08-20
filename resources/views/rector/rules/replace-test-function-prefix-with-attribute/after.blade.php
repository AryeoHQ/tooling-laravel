@verbatim
class SomeTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function onePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1 + 1);
    }
}
@endverbatim
