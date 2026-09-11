<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('clientes');

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Clientes - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <?php include '../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../templates/sidebar.php'; ?>

        <main class="main-content">

            <!-- =====================================================
             CABECERA
        ====================================================== -->

            <div class="page-header">

                <div>

                    <h1>Clientes</h1>

                    <p>
                        Gestión y consulta de los clientes registrados
                    </p>

                </div>

                <div class="page-header-actions">

                    <div class="page-date">
                        7 septiembre 2026
                    </div>

                    <button type="button" class="config-save-button">
                        + Nuevo cliente
                    </button>

                </div>

            </div>


            <!-- =====================================================
             TABLA DE CLIENTES
        ====================================================== -->

            <section class="panel clientes-table-panel">

                <div class="panel-header">

                    <div>
                        <h2>Clientes registrados</h2>
                        <p id="clientesContador">
                            24 clientes encontrados
                        </p>
                    </div>

                    <div class="panel-header-actions" style="display:flex; gap:10px; align-items:center;">

                        <div class="clientes-por-pagina">
                            <label for="selectorPorPagina">Mostrar:</label>
                            <select id="selectorPorPagina" class="por-pagina-select">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>

                        <button type="button" class="panel-action" id="btnLimpiarFiltros">
                            Limpiar filtros
                        </button>

                    </div>

                </div>

                <div class="clientes-table-container">

                    <table class="clientes-table">

                        <thead>

                            <!-- =================================================
                             CABECERAS
                        ================================================== -->

                            <tr>

                                <th>
                                    <div class="table-header-content">
                                        <span>Cliente</span>

                                        <button type="button" class="sort-button" data-column="0"
                                            title="Ordenar por cliente">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th>
                                    <div class="table-header-content">
                                        <span>Identificación</span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por identificación">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th>
                                    <div class="table-header-content">
                                        <span>Correo</span>

                                        <button type="button" class="sort-button" data-column="2"
                                            title="Ordenar por correo">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th>
                                    <div class="table-header-content">
                                        <span>Comercializadora</span>

                                        <button type="button" class="sort-button" data-column="3"
                                            title="Ordenar por comercializadora">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th>
                                    <div class="table-header-content">
                                        <span>Tarifa</span>

                                        <button type="button" class="sort-button" data-column="4"
                                            title="Ordenar por tarifa">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th>
                                    <div class="table-header-content">
                                        <span>Estado</span>

                                        <button type="button" class="sort-button" data-column="5"
                                            title="Ordenar por estado">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th>
                                    <span>Acciones</span>
                                </th>

                            </tr>


                            <!-- =================================================
                             FILTROS POR COLUMNA
                        ================================================== -->

                            <tr class="clientes-filter-row-table">

                                <!-- Cliente -->
                                <th>

                                    <input type="text" class="column-filter" data-column="0"
                                        placeholder="Buscar cliente...">

                                </th>


                                <!-- Identificación -->
                                <th>

                                    <input type="text" class="column-filter" data-column="1" placeholder="DNI / CIF...">

                                </th>


                                <!-- Correo -->
                                <th>

                                    <input type="text" class="column-filter" data-column="2"
                                        placeholder="Buscar correo...">

                                </th>


                                <!-- Comercializadora -->
                                <th>

                                    <select class="column-filter" data-column="3">

                                        <option value="">
                                            Todas
                                        </option>

                                        <option value="Endesa">
                                            Endesa
                                        </option>

                                        <option value="Iberdrola">
                                            Iberdrola
                                        </option>

                                        <option value="Naturgy">
                                            Naturgy
                                        </option>

                                        <option value="Repsol">
                                            Repsol
                                        </option>

                                        <option value="TotalEnergies">
                                            TotalEnergies
                                        </option>

                                    </select>

                                </th>


                                <!-- Tarifa -->
                                <th>

                                    <select class="column-filter" data-column="4">

                                        <option value="">
                                            Todas
                                        </option>

                                        <option value="PVPC">
                                            PVPC
                                        </option>

                                        <option value="Mercado libre">
                                            Mercado libre
                                        </option>

                                        <option value="Tarifa fija">
                                            Tarifa fija
                                        </option>

                                    </select>

                                </th>


                                <!-- Estado -->
                                <th>

                                    <select class="column-filter" data-column="5">

                                        <option value="">
                                            Todos
                                        </option>

                                        <option value="Activo">
                                            Activo
                                        </option>

                                        <option value="Pendiente">
                                            Pendiente
                                        </option>

                                        <option value="Inactivo">
                                            Inactivo
                                        </option>

                                    </select>

                                </th>


                                <!-- Acciones -->
                                <th></th>

                            </tr>

                        </thead>


                        <tbody id="clientesBody">


                            <!-- CLIENTE 1 -->

                            <tr>

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            EG
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Electricidad García S.L.
                                            </strong>

                                            <span>
                                                Empresa
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>B12345678</td>

                                <td>cliente@electricidadgarcia.es</td>

                                <td>Endesa</td>

                                <td>
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td>

                                    <div class="cliente-actions">

                                        <button type="button" class="table-action-button">
                                            Ver
                                        </button>

                                        <button type="button" class="table-action-button">
                                            Editar
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- CLIENTE 2 -->

                            <tr>

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            SN
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Solar Norte S.A.
                                            </strong>

                                            <span>
                                                Empresa
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>A28012345</td>

                                <td>contacto@solarnorte.es</td>

                                <td>Iberdrola</td>

                                <td>
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td>

                                    <div class="cliente-actions">

                                        <button type="button" class="table-action-button">
                                            Ver
                                        </button>

                                        <button type="button" class="table-action-button">
                                            Editar
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- CLIENTE 3 -->

                            <tr>

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            LM
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Luis Martín
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>12345678A</td>

                                <td>luis.martin@email.es</td>

                                <td>Naturgy</td>

                                <td>
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge cliente-pending">
                                        Pendiente
                                    </span>
                                </td>

                                <td>

                                    <div class="cliente-actions">

                                        <button type="button" class="table-action-button">
                                            Ver
                                        </button>

                                        <button type="button" class="table-action-button">
                                            Editar
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- CLIENTE 4 -->

                            <tr>

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            MG
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                María González
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>45678912B</td>

                                <td>maria.gonzalez@email.es</td>

                                <td>Repsol</td>

                                <td>
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td>

                                    <div class="cliente-actions">

                                        <button type="button" class="table-action-button">
                                            Ver
                                        </button>

                                        <button type="button" class="table-action-button">
                                            Editar
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- CLIENTE 5 -->

                            <tr>

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            JL
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Juan Carlos López
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>78451236C</td>

                                <td>juan.lopez@email.es</td>

                                <td>TotalEnergies</td>

                                <td>
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge cliente-inactive">
                                        Inactivo
                                    </span>
                                </td>

                                <td>

                                    <div class="cliente-actions">

                                        <button type="button" class="table-action-button">
                                            Ver
                                        </button>

                                        <button type="button" class="table-action-button">
                                            Editar
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- CLIENTE 6 -->

                            <tr>

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            AR
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Ana Rodríguez
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>52987461D</td>

                                <td>ana.rodriguez@email.es</td>

                                <td>Endesa</td>

                                <td>
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td>

                                    <div class="cliente-actions">

                                        <button type="button" class="table-action-button">
                                            Ver
                                        </button>

                                        <button type="button" class="table-action-button">
                                            Editar
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- CLIENTES 7-24 -->

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">CR</div>
                                        <div class="cliente-info">
                                            <strong>Carlos Ruiz</strong>
                                            <span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>56874123E</td>
                                <td>carlos.ruiz@email.es</td>
                                <td>Iberdrola</td>
                                <td><span class="tarifa-badge tarifa-libre">Mercado libre</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">LF</div>
                                        <div class="cliente-info"><strong>Laura
                                                Fernández</strong><span>Particular</span></div>
                                    </div>
                                </td>
                                <td>39481725F</td>
                                <td>laura.fernandez@email.es</td>
                                <td>Endesa</td>
                                <td><span class="tarifa-badge tarifa-pvpc">PVPC</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">MS</div>
                                        <div class="cliente-info"><strong>Miguel Sánchez</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>61528479G</td>
                                <td>miguel.sanchez@email.es</td>
                                <td>Naturgy</td>
                                <td><span class="tarifa-badge tarifa-fija">Tarifa fija</span></td>
                                <td><span class="status-badge cliente-pending">Pendiente</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">EN</div>
                                        <div class="cliente-info"><strong>Comercial Energía Norte
                                                S.L.</strong><span>Empresa</span></div>
                                    </div>
                                </td>
                                <td>B76543210</td>
                                <td>contacto@energianorte.es</td>
                                <td>Repsol</td>
                                <td><span class="tarifa-badge tarifa-libre">Mercado libre</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">PR</div>
                                        <div class="cliente-info"><strong>Patricia
                                                Romero</strong><span>Particular</span></div>
                                    </div>
                                </td>
                                <td>48291736H</td>
                                <td>patricia.romero@email.es</td>
                                <td>TotalEnergies</td>
                                <td><span class="tarifa-badge tarifa-pvpc">PVPC</span></td>
                                <td><span class="status-badge cliente-inactive">Inactivo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">JM</div>
                                        <div class="cliente-info"><strong>Javier Moreno</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>75163829J</td>
                                <td>javier.moreno@email.es</td>
                                <td>Endesa</td>
                                <td><span class="tarifa-badge tarifa-fija">Tarifa fija</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">AT</div>
                                        <div class="cliente-info"><strong>Alba Torres</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>20847591K</td>
                                <td>alba.torres@email.es</td>
                                <td>Iberdrola</td>
                                <td><span class="tarifa-badge tarifa-libre">Mercado libre</span></td>
                                <td><span class="status-badge cliente-pending">Pendiente</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">DS</div>
                                        <div class="cliente-info"><strong>Distribuciones Sol
                                                S.A.</strong><span>Empresa</span></div>
                                    </div>
                                </td>
                                <td>A14567893</td>
                                <td>admin@distribucionessol.es</td>
                                <td>Naturgy</td>
                                <td><span class="tarifa-badge tarifa-pvpc">PVPC</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">SN</div>
                                        <div class="cliente-info"><strong>Sergio Navarro</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>63729184L</td>
                                <td>sergio.navarro@email.es</td>
                                <td>Repsol</td>
                                <td><span class="tarifa-badge tarifa-fija">Tarifa fija</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">CV</div>
                                        <div class="cliente-info"><strong>Cristina Vega</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>51473826M</td>
                                <td>cristina.vega@email.es</td>
                                <td>TotalEnergies</td>
                                <td><span class="tarifa-badge tarifa-libre">Mercado libre</span></td>
                                <td><span class="status-badge cliente-inactive">Inactivo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">HV</div>
                                        <div class="cliente-info"><strong>Hogar Verde S.L.</strong><span>Empresa</span>
                                        </div>
                                    </div>
                                </td>
                                <td>B38945127</td>
                                <td>info@hogarverde.es</td>
                                <td>Endesa</td>
                                <td><span class="tarifa-badge tarifa-pvpc">PVPC</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">DI</div>
                                        <div class="cliente-info"><strong>Daniel
                                                Iglesias</strong><span>Particular</span></div>
                                    </div>
                                </td>
                                <td>72918453N</td>
                                <td>daniel.iglesias@email.es</td>
                                <td>Iberdrola</td>
                                <td><span class="tarifa-badge tarifa-fija">Tarifa fija</span></td>
                                <td><span class="status-badge cliente-pending">Pendiente</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">MC</div>
                                        <div class="cliente-info"><strong>Marta Castillo</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>36192748P</td>
                                <td>marta.castillo@email.es</td>
                                <td>Naturgy</td>
                                <td><span class="tarifa-badge tarifa-libre">Mercado libre</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">SD</div>
                                        <div class="cliente-info"><strong>Servicios Delta
                                                S.A.</strong><span>Empresa</span></div>
                                    </div>
                                </td>
                                <td>A67192834</td>
                                <td>contacto@serviciosdelta.es</td>
                                <td>Repsol</td>
                                <td><span class="tarifa-badge tarifa-pvpc">PVPC</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">RD</div>
                                        <div class="cliente-info"><strong>Raúl Domínguez</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>84517362Q</td>
                                <td>raul.dominguez@email.es</td>
                                <td>TotalEnergies</td>
                                <td><span class="tarifa-badge tarifa-fija">Tarifa fija</span></td>
                                <td><span class="status-badge cliente-inactive">Inactivo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">EP</div>
                                        <div class="cliente-info"><strong>Elena Prieto</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>49271835R</td>
                                <td>elena.prieto@email.es</td>
                                <td>Endesa</td>
                                <td><span class="tarifa-badge tarifa-libre">Mercado libre</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">PF</div>
                                        <div class="cliente-info"><strong>Pablo Ferrer</strong><span>Particular</span>
                                        </div>
                                    </div>
                                </td>
                                <td>68392417S</td>
                                <td>pablo.ferrer@email.es</td>
                                <td>Iberdrola</td>
                                <td><span class="tarifa-badge tarifa-pvpc">PVPC</span></td>
                                <td><span class="status-badge cliente-pending">Pendiente</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar">EC</div>
                                        <div class="cliente-info"><strong>Electricidad Centro
                                                S.L.</strong><span>Empresa</span></div>
                                    </div>
                                </td>
                                <td>B49281736</td>
                                <td>administracion@electricidadcentro.es</td>
                                <td>Naturgy</td>
                                <td><span class="tarifa-badge tarifa-fija">Tarifa fija</span></td>
                                <td><span class="status-badge cliente-active">Activo</span></td>
                                <td>
                                    <div class="cliente-actions"><button type="button"
                                            class="table-action-button">Ver</button><button type="button"
                                            class="table-action-button">Editar</button></div>
                                </td>
                            </tr>


                        </tbody>

                    </table>

                </div>


                <!-- =====================================================
                 PAGINACIÓN
            ====================================================== -->

                <div class="clientes-pagination">

                    <span id="clientesMostrando">
                        Mostrando 24 de 186 clientes
                    </span>

                    <div class="pagination-buttons">

                        <button type="button" class="pagination-button disabled" data-page="prev">
                            ‹
                        </button>

                        <button type="button" class="pagination-button active" data-page="1">
                            1
                        </button>

                        <button type="button" class="pagination-button" data-page="2">
                            2
                        </button>

                        <button type="button" class="pagination-button" data-page="3">
                            3
                        </button>

                        <button type="button" class="pagination-button" data-page="4">
                            4
                        </button>

                        <button type="button" class="pagination-button" data-page="5">
                            5
                        </button>

                        <button type="button" class="pagination-button" data-page="next">
                            ›
                        </button>

                    </div>

                </div>

            </section>

        </main>

    </div>


    <?php include '../templates/footer.php'; ?>


    <script src="../js/clientes.js"></script>

</body>

</html>