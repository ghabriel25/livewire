<?php

namespace Livewire\Features\SupportWireDirty;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_dirty_cleans_up_when_its_element_is_removed()
    {
        Livewire::visit(new class extends Component {
            public $foo = '';
            public $showIndicator = true;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input wire:model.lazy="foo" dusk="foo">

                        @if ($showIndicator)
                            <span wire:dirty wire:target="foo" dusk="dirty">Dirty Indicator</span>
                        @endif

                        <button type="button" wire:click="$toggle('showIndicator')" dusk="toggle"></button>
                        <button type="button" dusk="dummy"></button>
                    </div>
                HTML;
            }
        })
            ->assertPresent('@dirty')
            ->assertMissing('@dirty')
            ->tap(fn ($b) => $b->script("
                window.__removedEl = document.querySelector('[dusk=\"dirty\"]')
            "))
            ->waitForLivewire()->click('@toggle')
            ->assertNotPresent('@dirty')

            ->type('@foo', 'bar')
            // leaked effect would have display inline-block
            ->assertScript('window.__removedEl.style.display', '')
            ->waitForLivewire()->click('@dummy')
            ->assertNotPresent('@dirty')
            ->clear('@foo')

            ->waitForLivewire()->click('@toggle')
            ->assertPresent('@dirty')
            ->assertMissing('@dirty')
            ->type('@foo', 'baz')
            ->waitFor('@dirty')
            ->waitForLivewire()->click('@dummy')
            ->waitUntilMissing('@dirty')
        ;
    }
}
