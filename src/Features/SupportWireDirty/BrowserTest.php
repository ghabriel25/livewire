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
            ->type('@foo', 'bar')
            ->waitFor('@dirty')
            ->waitForLivewire()->click('@dummy')
            ->waitUntilMissing('@dirty')

            ->waitForLivewire()->click('@toggle')
            ->waitUntilMissing('@dirty')

            ->type('@foo', 'baz')
            ->waitForLivewire()->click('@dummy')
            ->assertNotPresent('@dirty')
            ->clear('@foo')

            ->waitForLivewire()->click('@toggle')
            ->assertMissing('@dirty')
            ->type('@foo', 'qux')
            ->waitFor('@dirty') // fails without cleanup
            ->waitForLivewire()->click('@dummy')
            ->waitUntilMissing('@dirty')
        ;
    }
}
