<?php

namespace App\Providers;

use App\Logic\Macros\Macros;
use Collective\Html\HtmlServiceProvider;

/**
 * Class MacroServiceProvider.
 */
class MacroServiceProvider extends HtmlServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Macros must be loaded after the HTMLServiceProvider's
        // register method is called. Otherwise, csrf tokens
        // will not be generated
        parent::register();

        // Load HTML Macros
        require base_path().'/app/Logic/Macros/HtmlMacros.php';
    }
    
    public function boot()
    {
        \Form::macro('selectNull', function ($name, $list = [], $selected = null, array $selectAttributes = [], array $optionsAttributes = [], array $optgroupsAttributes = []) {
            $selected = $this->old($name) == null && array_key_exists($name, $this->getSessionStore()->getOldInput()) ? null : $selected;
        
            return $this->select($name, $list, $selected, $selectAttributes, $optionsAttributes, $optgroupsAttributes);
        });
    }
}
