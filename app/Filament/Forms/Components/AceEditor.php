<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Closure;

class AceEditor extends Field
{
    protected string $view = 'filament.forms.components.ace-editor';

    protected string | Closure | null $language = 'html';
    protected string | Closure | null $theme = 'monokai';
    protected string | Closure | null $height = '500px';

    public function language(string | Closure | null $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function theme(string | Closure | null $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function height(string | Closure | null $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getLanguage(): string
    {
        return $this->evaluate($this->language) ?? 'html';
    }

    public function getTheme(): string
    {
        return $this->evaluate($this->theme) ?? 'monokai';
    }

    public function getHeight(): string
    {
        return $this->evaluate($this->height) ?? '500px';
    }
}
