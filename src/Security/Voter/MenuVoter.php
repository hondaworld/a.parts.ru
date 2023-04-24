<?php


namespace App\Security\Voter;


use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\Request;

class MenuVoter implements VoterInterface
{
    /**
     * @var Request
     */
    private Request $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    public function matchItem(ItemInterface $item): ?bool
    {

        if (null === $item->getUri()) {
            return null;
        }

        if ($item->getExtra('pattern') && \preg_match('#' . $item->getExtra('pattern') . '#', $this->request->getPathInfo())) {
            return true;
        }

        return null;
    }

}