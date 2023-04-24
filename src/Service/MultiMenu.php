<?php


namespace App\Service;


use App\ReadModel\DropDownList;

class MultiMenu
{
    public const RAZD = ' » ';

    /**
     * Возвращает заголовок категорий начиная с первого родительского
     *
     * @param $data - данные в виде массива. Ключ - id, значение - массив остальных данных
     * @param $parent_id - родительский элемент текущего элемента
     * @param $name - название поля в БД, которое нужно для отображения
     * @return string
     */
    public function getTitle(array $data, int $parent_id, string $name): string
    {
        $arr = $this->getParents($data, $parent_id);
        $arrNames = [];
        foreach ($arr as $item) {
            $arrNames[] = $item[$name];
        }
        return implode(self::RAZD, $arrNames);
    }

    /**
     * Возвращает массив BreadCrumb.
     *
     * @param $data - данные в виде массива. Ключ - id, значение - массив остальных данных
     * @param $parent_id - родительский элемент текущего элемента
     * @param $name - название поля в БД, которое нужно для отображения
     * @param string $lastChild - последний элемент, который не будет содержать адрес.
     *          Если свойство пустое, то последним элементом станет самый молодой родитель в массиве.
     * @return array
     */
    public function getBreadCrumb($data, $parent_id, $name, string $lastChild = ''): array
    {
        $arr = $this->getParents($data, $parent_id);
        $arrBreadCrumb = [];
        foreach ($arr as $item) {
            $arrBreadCrumb[] = ['name' => $item[$name], 'url' => true, 'data' => $item];
        }
        if (count($arrBreadCrumb) > 0 && $lastChild == '') $arrBreadCrumb[count($arrBreadCrumb) - 1]['url'] = false;
        if ($lastChild != '') $arrBreadCrumb[]['name'] = $lastChild;
        return $arrBreadCrumb;
    }

    /**
     * Формирует массив от первого родителя к элементу, родитель которого указан.
     * Метод нужен для отображения заголовка и навигационной цепочке.
     *
     * @param $data - данные в виде массива. Ключ - id, значение - массив остальных данных
     * @param $parent_id - родительский элемент текущего элемента
     * @return array
     */
    public function getParents(array $data, int $parent_id): array
    {
        $arr = [];
        $p_id = $parent_id;

        while ($p_id != 0) {
            if ($data[$p_id]) {
                $data[$p_id]['id'] = $p_id;
                $arr[] = $data[$p_id];
                $p_id = $data[$p_id]['parent_id'];
            } else {
                break;
            }
        }

        krsort($arr);
        return $arr;
    }

    /**
     * Метод формирует дерево для отображения в списке формы.
     *
     * @param array $data - данные в виде массива. Ключ - id, значение - массив остальных данных
     * @param string $name - название поля в БД, которое нужно для отображения
     * @param int|null $id_exclude
     * @return array
     */
    public function getDropDownList(array $data, string $name, int $id_exclude = null): array
    {
        return $this->generateIndents($this->getTree($data), $name, '', $id_exclude);
    }

    /**
     * Метод формирует многоуровневое меню.
     *
     * @param $data - данные в виде массива. Ключ - id, значение - массив остальных данных
     * @return array
     */
    public function getMenu(array $data): array
    {
        return $this->getTree($data);
    }

    /**
     * Рекурсивный метод для формирования отступов. Чем больше потомков, тем больше отступ.
     *
     * @param array $data - данные в виде массива. Ключ - id, значение - массив остальных данных
     * @param string $name - название поля в БД, которое нужно для отображения
     * @param string $spacer - отступ
     * @param int|null $id_exclude
     * @return array
     */
    protected function generateIndents(array $data, string $name, string $spacer = '', int $id_exclude = null): array
    {
        $indents = [];

        foreach ($data as $id => $node) {
            if ($id_exclude != $id) {
                $item = $node;
                unset($item['childs']);
                $item['id'] = $id;
                $indents[] = new DropDownList($id, $spacer . $node[$name], $item);
                if (isset($node['childs'])) $indents = array_merge($indents, $this->generateIndents($node['childs'], $name, $spacer . self::RAZD, $id_exclude));
            }
        }

        return $indents;
    }

    /**
     * Метод построения дерева из массива от Tommy Lacroix
     *
     * @param array $dataset - данные в виде массива. Ключ - id, значение - массив остальных данных
     * @return array
     */
    protected function getTree(array $dataset): array
    {
        $tree = [];

        foreach ($dataset as $id => &$node) {
            //Если нет вложений
            if (!$node['parent_id']) {
                $tree[$id] = &$node;
            } else {
                //Если есть потомки то перебираем массив
                $dataset[$node['parent_id']]['childs'][$id] = &$node;
            }
        }
        return $tree;
    }
}