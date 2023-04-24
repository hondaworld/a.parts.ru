<?php


namespace App\Service;


use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;

class ManagerSettings
{
    /**
     * @var ManagerFetcher
     */
    private $managers;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var Request
     */
    private $request;

    public function __construct(ManagerFetcher $managers, Security $security)
    {
        $this->managers = $managers;
        $this->security = $security;
        $this->request = Request::createFromGlobals();
    }

    private function getSettings(): array
    {
        return $this->security->getUser()->getSettings();
    }

    private function putSettings(array $settings): void
    {
        $this->managers->setSettings($this->security->getUser()->getId(), json_encode($settings));
    }

    public function get(string $model): array
    {
        $session = new Session();
        $settings = $this->getSettings();

        if ($this->request->query->get('sort')) {
            $settings[$model]['sort'] = $this->request->query->get('sort');
        }

        if ($this->request->query->get('direction')) {
            $settings[$model]['direction'] = $this->request->query->get('direction');
        }

        if ($this->request->query->get('form')) {
            if (isset($this->request->query->get('form')['inPage'])) {
                $settings[$model]['inPage'] = $this->request->query->get('form')['inPage'];
            }
            //$session = $this->request->getSession();
            $session->set('filter/' . $model, $this->request->query->get('form'));
        }

        if ($this->request->query->getInt('page')) {
            $session->set('page/' . $model, $this->request->query->getInt('page'));
        } else {
            $session->remove('page/' . $model);
        }

        if ($this->request->query->getInt('reset') == 1) {
            $session->remove('filter/' . $model);
        }

        if (isset($settings[$model])) {
            $this->putSettings($settings);
            return $settings[$model];
        }
        return [];
    }

    public function getCols(string $model, array $allCols): array
    {
        $settings = $this->getSettings();

        if ($this->request->get('cols')) {
            $cols = $this->request->get('cols');
            $hideCols = [];
            foreach ($allCols as $allCol) {
                if (!in_array($allCol, $cols)) $hideCols[] = $allCol;
            }
            $settings[$model]['hideCols'] = $hideCols;
        }

        if (isset($settings[$model])) {
            $this->putSettings($settings);
            return $settings[$model];
        }
        return [];
    }

    public function changeTheme(): string
    {
        $settings = $this->getSettings();

        foreach (['page-header', 'sidebar', 'theme-css'] as $theme) {
            if ($this->request->get($theme) !== null) {
                $settings['theme'][$theme] = $this->request->get($theme);
            }
        }

        $this->putSettings($settings);

        if ($this->request->get('theme-css') !== null) return 'reload';
        return '';
    }
}