<?php

final class PhabricatorSortTableUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Bảng phân loại');
  }

  public function getDescription() {
    return pht('Sử dụng bảng phân loại.');
  }

  public function renderExample() {

    $rows = array(
      array(
        'make'    => 'Honda',
        'model'   => 'Civic',
        'year'    => 2004,
        'price'   => 3199,
        'color'   => pht('Blue'),
      ),
      array(
        'make'    => 'Ford',
        'model'   => 'Focus',
        'year'    => 2001,
        'price'   => 2549,
        'color'   => pht('Red'),
      ),
      array(
        'make'    => 'Toyota',
        'model'   => 'Camry',
        'year'    => 2009,
        'price'   => 4299,
        'color'   => pht('Black'),
      ),
      array(
        'make'    => 'NASA',
        'model'   => 'Shuttle',
        'year'    => 1998,
        'price'   => 1000000000,
        'color'   => pht('White'),
      ),
    );

    $request = $this->getRequest();

    $orders = array(
      'make',
      'model',
      'year',
      'price',
    );

    $sort = $request->getStr('sort');
    list($sort, $reverse) = AphrontTableView::parseSort($sort);
    if (!in_array($sort, $orders)) {
      $sort = 'make';
    }

    $rows = isort($rows, $sort);
    if ($reverse) {
      $rows = array_reverse($rows);
    }

    $table = new AphrontTableView($rows);
    $table->setHeaders(
      array(
        pht('Make'),
        pht('Kiểu'),
        pht('Năm'),
        pht('Giá'),
        pht('Màu'),
      ));
    $table->setColumnClasses(
      array(
        '',
        'wide',
        'n',
        'n',
        '',
      ));
    $table->makeSortable(
      $request->getRequestURI(),
      'sort',
      $sort,
      $reverse,
      $orders);

    $panel = new PHUIObjectBoxView();
    $panel->setHeaderText(pht('Bảng phân loại của phương tiện'));
    $panel->setTable($table);

    return $panel;
  }
}
