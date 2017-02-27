<?php
$this->layout = false;
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sandbox</title>

    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>
    <?= $this->Html->css('home.css') ?>
    <link href="https://fonts.googleapis.com/css?family=Raleway:500i|Roboto:300,400,700|Roboto+Mono" rel="stylesheet">
</head>
<body class="home">

<header class="row">
    <div class="header-image"><?= $this->Html->image('cake.logo.svg') ?></div>
    <div class="header-title">
        <h1>Welcome Sandbox</h1>
    </div>
</header>

<div class="row">
    <div class="columns large-6">
        <h3>Editing Pager Sandbox</h3>
        <div class="paging">
            <?=$this->Paginator->first('<< '); ?>
            <?=$this->Paginator->prev('< '); ?>
            <?=$this->Paginator->numbers(); ?>
            <?=$this->Paginator->next(' >'); ?>
            <?=$this->Paginator->last(' >>'); ?>
        </div>
        <table>
            <tr>
                <th>ID<?=$this->Paginator->sort('id')?></th>
                <th>Name<?=$this->Paginator->sort('tname')?></th>
            </tr>
            <?php foreach((array)$items as $item): ?>
                <tr>
                    <td><?=$item['id']?></td><td><?=$item['tname']?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

</body>
</html>
