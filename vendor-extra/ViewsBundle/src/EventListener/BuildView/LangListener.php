<?php

declare(strict_types=1);

namespace Libero\ViewsBundle\EventListener\BuildView;

use FluentDOM\DOM\Attribute;
use Libero\ViewsBundle\Event\BuildViewEvent;
use Libero\ViewsBundle\Views\TemplateView;
use Punic\Misc;
use function array_merge;

final class LangListener
{
    public function onBuildView(BuildViewEvent $event) : void
    {
        $view = $event->getView();

        if (!$view instanceof TemplateView) {
            return;
        }

        if ($view->hasArgument('attributes') && !empty($view->getArgument('attributes')['lang'])) {
            return;
        }

        $object = $event->getObject();

        $lang = $object->ownerDocument->xpath()
            ->firstOf('ancestor-or-self::*[@xml:lang][1]/@xml:lang', $object);

        if (!$lang instanceof Attribute || $lang->nodeValue === $view->getContext('lang')) {
            return;
        }

        $context = ['lang' => $lang->nodeValue];
        $attributes = array_merge($view->getArgument('attributes') ?? [], ['lang' => $context['lang']]);
        $dir = 'right-to-left' === Misc::getCharacterOrder($context['lang']) ? 'rtl' : 'ltr';
        if ($view->getContext('dir') !== $dir) {
            $context['dir'] = $dir;
            $attributes['dir'] = $dir;
        }

        $event->setView($view->withArgument('attributes', $attributes)->withContext($context));
    }
}
