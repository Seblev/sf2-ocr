<?php

namespace OC\PlatformBundle\Antispam;

class OCAntispam extends \Twig_Extension
{

    protected $mailer;
    protected $locale;
    protected $nbForSpam;

    // Dans le constructeur, on retire $locale des arguments
    public function __construct(\Swift_Mailer $mailer, $nbForSpam)
    {
        $this->mailer = $mailer;
        $this->nbForSpam = (int)$nbForSpam;
    }

    // Et on ajoute un setter
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getFunctions()
    {
        return array(
            'checkIfSpam' => new \Twig_Function_Method($this, 'isSpam')
        );
    }

    public function getName()
    {
        return 'OCAntispam';
    }
}