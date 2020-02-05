<?php

namespace JasperFW\JasperFW\Renderer\ViewHelper;

interface ViewHelperInterface
{
    /**
     * The init function is called when a view helper is loaded. This function should look in the configuration to find
     * any initial settings for the view helper.
     *
     * @param null|string $configuration The name of the settings in the config array
     */
    public function init(?string $configuration = null): void;

    /**
     * Get the name of the parent viewhelper collection
     *
     * @return string|null
     */
    public function getParent(): ?string;

    /**
     * @return string
     */
    public function __toString(): string;
}