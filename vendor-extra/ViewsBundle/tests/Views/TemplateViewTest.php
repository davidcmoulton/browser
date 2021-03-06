<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use ArrayAccess;
use BadMethodCallException;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use Traversable;
use function iterator_to_array;

final class TemplateViewTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view() : void
    {
        $view = new TemplateView('template');

        $this->assertInstanceOf(View::class, $view);
    }

    /**
     * @test
     */
    public function it_has_a_template() : void
    {
        $view = new TemplateView('template');

        $this->assertSame('template', $view->getTemplate());
    }

    /**
     * @test
     */
    public function it_has_arguments() : void
    {
        $view = new TemplateView('template', ['foo' => 'bar']);

        $this->assertTrue($view->hasArgument('foo'));
        $this->assertFalse($view->hasArgument('bar'));

        $this->assertSame('bar', $view->getArgument('foo'));
        $this->assertNull($view->getArgument('bar'));
        $this->assertEquals(['foo' => 'bar'], $view->getArguments());

        $view = $view->withArgument('foo', ['baz' => 'qux']);
        $this->assertSame(['baz' => 'qux'], $view->getArgument('foo'));
        $this->assertEquals(['foo' => ['baz' => 'qux']], $view->getArguments());

        $view = $view->withArguments(['foo' => ['quux' => 'quuz']]);
        $this->assertSame(['quux' => 'quuz'], $view->getArgument('foo'));
        $this->assertEquals(['foo' => ['quux' => 'quuz']], $view->getArguments());
    }

    /**
     * @test
     */
    public function it_has_context() : void
    {
        $view = new TemplateView('template', [], ['foo' => 'bar']);

        $this->assertTrue($view->hasContext('foo'));
        $this->assertFalse($view->hasContext('bar'));

        $this->assertSame('bar', $view->getContext('foo'));
        $this->assertNull($view->getContext('bar'));
        $this->assertEquals(['foo' => 'bar'], $view->getContext());

        $view = $view->withContext(['foo' => ['baz' => 'qux']]);
        $this->assertSame(['baz' => 'qux'], $view->getContext('foo'));
        $this->assertEquals(['foo' => ['baz' => 'qux']], $view->getContext());

        $view = $view->withContext(['foo' => ['quux' => 'quuz']]);
        $this->assertSame(['quux' => 'quuz'], $view->getContext('foo'));
        $this->assertEquals(['foo' => ['quux' => 'quuz']], $view->getContext());
    }

    /**
     * @test
     */
    public function it_is_array_accessible() : void
    {
        $view = new TemplateView('template', ['foo' => 'bar', 'baz' => ['qux']]);

        $this->assertInstanceOf(ArrayAccess::class, $view);

        $this->assertArrayHasKey('template', $view);
        $this->assertSame('template', $view['template']);
        $this->assertArrayHasKey('arguments', $view);
        $this->assertSame(['foo' => 'bar', 'baz' => ['qux']], $view['arguments']);
        $this->assertArrayNotHasKey('quux', $view);
        $this->assertNull($view['quux']);
    }

    /**
     * @test
     * @dataProvider immutableProvider
     */
    public function it_is_immutable(callable $action) : void
    {
        $view = new TemplateView('template');

        $this->expectException(BadMethodCallException::class);

        $action($view);
    }

    public function immutableProvider() : iterable
    {
        yield 'set' => [
            static function (TemplateView $view) : void {
                $view['foo'] = 'bar';
            },
        ];
        yield 'unset' => [
            static function (TemplateView $view) : void {
                unset($view['foo']);
            },
        ];
    }

    /**
     * @test
     */
    public function it_is_traversable() : void
    {
        $view = new TemplateView('template', ['foo' => 'bar', 'baz' => ['qux']]);

        $this->assertInstanceOf(Traversable::class, $view);

        $expected = [
            'template' => 'template',
            'arguments' => [
                'foo' => 'bar',
                'baz' => ['qux'],
            ],
        ];

        $this->assertSame($expected, iterator_to_array($view));
    }
}
