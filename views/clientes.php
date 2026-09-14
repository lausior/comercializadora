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

                    <div class="panel-header-actions" style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">

                        <div class="clientes-por-pagina">
                            <label for="selectorPorPagina">Mostrar:</label>
                            <select id="selectorPorPagina" class="por-pagina-select">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>

                        <!-- Solo en escritorio: limpia los filtros de columna de la tabla -->
                        <button type="button" class="panel-action vista-escritorio" id="btnLimpiarFiltros">
                            Limpiar filtros
                        </button>

                        <!-- Solo en móvil: despliega el panel de filtros apilados -->
                        <button type="button" class="filtros-toggle-button vista-movil" id="btnToggleFiltros"
                            aria-expanded="false" aria-controls="panelFiltrosClientes">
                            <span>Filtros</span>
                            <span class="chevron">▾</span>
                        </button>

                    </div>

                </div>


                <!-- =================================================
                     PANEL DE FILTROS DESPLEGABLE (SOLO MÓVIL)
                     =================================================
                     Filtra las mismas columnas que la fila de
                     filtros de la tabla de escritorio (mismo
                     data-column), solo que apiladas en vertical
                     y ocultas hasta que se pulsa "Filtros".
                ================================================== -->

                <div class="filtros-panel vista-movil" id="panelFiltrosClientes" style="display:none;">

                    <div class="filtros-panel-campos">

                        <div class="filter-group">
                            <label for="filtroClienteMovil">Cliente</label>
                            <input type="text" id="filtroClienteMovil" class="column-filter" data-column="0"
                                placeholder="Buscar cliente...">
                        </div>

                        <div class="filter-group">
                            <label for="filtroIdentificacionMovil">Identificación</label>
                            <input type="text" id="filtroIdentificacionMovil" class="column-filter" data-column="1"
                                placeholder="DNI / CIF...">
                        </div>

                        <div class="filter-group">
                            <label for="filtroCorreoMovil">Correo</label>
                            <input type="text" id="filtroCorreoMovil" class="column-filter" data-column="2"
                                placeholder="Buscar correo...">
                        </div>

                        <div class="filter-group">
                            <label for="filtroComercializadoraMovil">Comercializadora</label>
                            <select id="filtroComercializadoraMovil" class="column-filter" data-column="3">

                                <option value="">Todas</option>
                                <option value="Endesa">Endesa</option>
                                <option value="Iberdrola">Iberdrola</option>
                                <option value="Naturgy">Naturgy</option>
                                <option value="Repsol">Repsol</option>
                                <option value="TotalEnergies">TotalEnergies</option>

                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filtroTarifaMovil">Tarifa</label>
                            <select id="filtroTarifaMovil" class="column-filter" data-column="4">

                                <option value="">Todas</option>
                                <option value="PVPC">PVPC</option>
                                <option value="Mercado libre">Mercado libre</option>
                                <option value="Tarifa fija">Tarifa fija</option>

                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filtroEstadoMovil">Estado</label>
                            <select id="filtroEstadoMovil" class="column-filter" data-column="5">

                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Inactivo">Inactivo</option>

                            </select>
                        </div>

                    </div>

                    <div class="filtros-panel-acciones">
                        <button type="button" class="panel-action" id="btnLimpiarFiltrosMovil">
                            Limpiar filtros
                        </button>
                    </div>

                </div>


                <div class="clientes-table-container">

                    <table class="clientes-table">

                        <thead>

                            <!-- =================================================
                                 CABECERAS (las columnas 2ª en adelante y la
                                 fila de filtros solo se ven en escritorio;
                                 en móvil el CSS las oculta y deja solo
                                 "Cliente")
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


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Identificación</span>

                                        <button type="button" class="sort-button" data-column="1"
                                            title="Ordenar por identificación">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Correo</span>

                                        <button type="button" class="sort-button" data-column="2"
                                            title="Ordenar por correo">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Comercializadora</span>

                                        <button type="button" class="sort-button" data-column="3"
                                            title="Ordenar por comercializadora">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Tarifa</span>

                                        <button type="button" class="sort-button" data-column="4"
                                            title="Ordenar por tarifa">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <div class="table-header-content">
                                        <span>Estado</span>

                                        <button type="button" class="sort-button" data-column="5"
                                            title="Ordenar por estado">
                                            ↕
                                        </button>
                                    </div>
                                </th>


                                <th class="vista-escritorio">
                                    <span>Acciones</span>
                                </th>

                            </tr>


                            <!-- =================================================
                                 FILTROS POR COLUMNA (SOLO ESCRITORIO)
                            ================================================== -->

                            <tr class="clientes-filter-row-table vista-escritorio">

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


                            <!-- fila-detalle: en móvil, pulsar la fila abre la
                                 tarjeta con toda la información; en escritorio
                                 no hace nada, ahí ya se ve todo. -->

                            <tr class="fila-detalle"
                                data-id="1"
                                data-nombre="Electricidad García S.L."
                                data-iniciales="EG"
                                data-tipo="Empresa"
                                data-identificacion="B12345678"
                                data-correo="cliente@electricidadgarcia.es"
                                data-comercializadora="Endesa"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

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

                                <td class="vista-escritorio">B12345678</td>

                                <td class="vista-escritorio">cliente@electricidadgarcia.es</td>

                                <td class="vista-escritorio">Endesa</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="2"
                                data-nombre="Solar Norte S.A."
                                data-iniciales="SN"
                                data-tipo="Empresa"
                                data-identificacion="A28012345"
                                data-correo="contacto@solarnorte.es"
                                data-comercializadora="Iberdrola"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

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

                                <td class="vista-escritorio">A28012345</td>

                                <td class="vista-escritorio">contacto@solarnorte.es</td>

                                <td class="vista-escritorio">Iberdrola</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="3"
                                data-nombre="Luis Martín"
                                data-iniciales="LM"
                                data-tipo="Particular"
                                data-identificacion="12345678A"
                                data-correo="luis.martin@email.es"
                                data-comercializadora="Naturgy"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Pendiente"
                                data-estadoclase="cliente-pending"
                            >

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

                                <td class="vista-escritorio">12345678A</td>

                                <td class="vista-escritorio">luis.martin@email.es</td>

                                <td class="vista-escritorio">Naturgy</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-pending">
                                        Pendiente
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="4"
                                data-nombre="María González"
                                data-iniciales="MG"
                                data-tipo="Particular"
                                data-identificacion="45678912B"
                                data-correo="maria.gonzalez@email.es"
                                data-comercializadora="Repsol"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

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

                                <td class="vista-escritorio">45678912B</td>

                                <td class="vista-escritorio">maria.gonzalez@email.es</td>

                                <td class="vista-escritorio">Repsol</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="5"
                                data-nombre="Juan Carlos López"
                                data-iniciales="JL"
                                data-tipo="Particular"
                                data-identificacion="78451236C"
                                data-correo="juan.lopez@email.es"
                                data-comercializadora="TotalEnergies"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Inactivo"
                                data-estadoclase="cliente-inactive"
                            >

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

                                <td class="vista-escritorio">78451236C</td>

                                <td class="vista-escritorio">juan.lopez@email.es</td>

                                <td class="vista-escritorio">TotalEnergies</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-inactive">
                                        Inactivo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="6"
                                data-nombre="Ana Rodríguez"
                                data-iniciales="AR"
                                data-tipo="Particular"
                                data-identificacion="52987461D"
                                data-correo="ana.rodriguez@email.es"
                                data-comercializadora="Endesa"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

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

                                <td class="vista-escritorio">52987461D</td>

                                <td class="vista-escritorio">ana.rodriguez@email.es</td>

                                <td class="vista-escritorio">Endesa</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="7"
                                data-nombre="Carlos Ruiz"
                                data-iniciales="CR"
                                data-tipo="Particular"
                                data-identificacion="56874123E"
                                data-correo="carlos.ruiz@email.es"
                                data-comercializadora="Iberdrola"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            CR
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Carlos Ruiz
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">56874123E</td>

                                <td class="vista-escritorio">carlos.ruiz@email.es</td>

                                <td class="vista-escritorio">Iberdrola</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="8"
                                data-nombre="Laura Fernández"
                                data-iniciales="LF"
                                data-tipo="Particular"
                                data-identificacion="39481725F"
                                data-correo="laura.fernandez@email.es"
                                data-comercializadora="Endesa"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            LF
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Laura Fernández
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">39481725F</td>

                                <td class="vista-escritorio">laura.fernandez@email.es</td>

                                <td class="vista-escritorio">Endesa</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="9"
                                data-nombre="Miguel Sánchez"
                                data-iniciales="MS"
                                data-tipo="Particular"
                                data-identificacion="61528479G"
                                data-correo="miguel.sanchez@email.es"
                                data-comercializadora="Naturgy"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Pendiente"
                                data-estadoclase="cliente-pending"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            MS
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Miguel Sánchez
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">61528479G</td>

                                <td class="vista-escritorio">miguel.sanchez@email.es</td>

                                <td class="vista-escritorio">Naturgy</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-pending">
                                        Pendiente
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="10"
                                data-nombre="Comercial Energía Norte S.L."
                                data-iniciales="EN"
                                data-tipo="Empresa"
                                data-identificacion="B76543210"
                                data-correo="contacto@energianorte.es"
                                data-comercializadora="Repsol"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            EN
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Comercial Energía Norte S.L.
                                            </strong>

                                            <span>
                                                Empresa
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">B76543210</td>

                                <td class="vista-escritorio">contacto@energianorte.es</td>

                                <td class="vista-escritorio">Repsol</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="11"
                                data-nombre="Patricia Romero"
                                data-iniciales="PR"
                                data-tipo="Particular"
                                data-identificacion="48291736H"
                                data-correo="patricia.romero@email.es"
                                data-comercializadora="TotalEnergies"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Inactivo"
                                data-estadoclase="cliente-inactive"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            PR
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Patricia Romero
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">48291736H</td>

                                <td class="vista-escritorio">patricia.romero@email.es</td>

                                <td class="vista-escritorio">TotalEnergies</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-inactive">
                                        Inactivo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="12"
                                data-nombre="Javier Moreno"
                                data-iniciales="JM"
                                data-tipo="Particular"
                                data-identificacion="75163829J"
                                data-correo="javier.moreno@email.es"
                                data-comercializadora="Endesa"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            JM
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Javier Moreno
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">75163829J</td>

                                <td class="vista-escritorio">javier.moreno@email.es</td>

                                <td class="vista-escritorio">Endesa</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="13"
                                data-nombre="Alba Torres"
                                data-iniciales="AT"
                                data-tipo="Particular"
                                data-identificacion="20847591K"
                                data-correo="alba.torres@email.es"
                                data-comercializadora="Iberdrola"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Pendiente"
                                data-estadoclase="cliente-pending"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            AT
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Alba Torres
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">20847591K</td>

                                <td class="vista-escritorio">alba.torres@email.es</td>

                                <td class="vista-escritorio">Iberdrola</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-pending">
                                        Pendiente
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="14"
                                data-nombre="Distribuciones Sol S.A."
                                data-iniciales="DS"
                                data-tipo="Empresa"
                                data-identificacion="A14567893"
                                data-correo="admin@distribucionessol.es"
                                data-comercializadora="Naturgy"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            DS
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Distribuciones Sol S.A.
                                            </strong>

                                            <span>
                                                Empresa
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">A14567893</td>

                                <td class="vista-escritorio">admin@distribucionessol.es</td>

                                <td class="vista-escritorio">Naturgy</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="15"
                                data-nombre="Sergio Navarro"
                                data-iniciales="SN"
                                data-tipo="Particular"
                                data-identificacion="63729184L"
                                data-correo="sergio.navarro@email.es"
                                data-comercializadora="Repsol"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            SN
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Sergio Navarro
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">63729184L</td>

                                <td class="vista-escritorio">sergio.navarro@email.es</td>

                                <td class="vista-escritorio">Repsol</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="16"
                                data-nombre="Cristina Vega"
                                data-iniciales="CV"
                                data-tipo="Particular"
                                data-identificacion="51473826M"
                                data-correo="cristina.vega@email.es"
                                data-comercializadora="TotalEnergies"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Inactivo"
                                data-estadoclase="cliente-inactive"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            CV
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Cristina Vega
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">51473826M</td>

                                <td class="vista-escritorio">cristina.vega@email.es</td>

                                <td class="vista-escritorio">TotalEnergies</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-inactive">
                                        Inactivo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="17"
                                data-nombre="Hogar Verde S.L."
                                data-iniciales="HV"
                                data-tipo="Empresa"
                                data-identificacion="B38945127"
                                data-correo="info@hogarverde.es"
                                data-comercializadora="Endesa"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            HV
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Hogar Verde S.L.
                                            </strong>

                                            <span>
                                                Empresa
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">B38945127</td>

                                <td class="vista-escritorio">info@hogarverde.es</td>

                                <td class="vista-escritorio">Endesa</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="18"
                                data-nombre="Daniel Iglesias"
                                data-iniciales="DI"
                                data-tipo="Particular"
                                data-identificacion="72918453N"
                                data-correo="daniel.iglesias@email.es"
                                data-comercializadora="Iberdrola"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Pendiente"
                                data-estadoclase="cliente-pending"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            DI
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Daniel Iglesias
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">72918453N</td>

                                <td class="vista-escritorio">daniel.iglesias@email.es</td>

                                <td class="vista-escritorio">Iberdrola</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-pending">
                                        Pendiente
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="19"
                                data-nombre="Marta Castillo"
                                data-iniciales="MC"
                                data-tipo="Particular"
                                data-identificacion="36192748P"
                                data-correo="marta.castillo@email.es"
                                data-comercializadora="Naturgy"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            MC
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Marta Castillo
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">36192748P</td>

                                <td class="vista-escritorio">marta.castillo@email.es</td>

                                <td class="vista-escritorio">Naturgy</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="20"
                                data-nombre="Servicios Delta S.A."
                                data-iniciales="SD"
                                data-tipo="Empresa"
                                data-identificacion="A67192834"
                                data-correo="contacto@serviciosdelta.es"
                                data-comercializadora="Repsol"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            SD
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Servicios Delta S.A.
                                            </strong>

                                            <span>
                                                Empresa
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">A67192834</td>

                                <td class="vista-escritorio">contacto@serviciosdelta.es</td>

                                <td class="vista-escritorio">Repsol</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="21"
                                data-nombre="Raúl Domínguez"
                                data-iniciales="RD"
                                data-tipo="Particular"
                                data-identificacion="84517362Q"
                                data-correo="raul.dominguez@email.es"
                                data-comercializadora="TotalEnergies"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Inactivo"
                                data-estadoclase="cliente-inactive"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            RD
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Raúl Domínguez
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">84517362Q</td>

                                <td class="vista-escritorio">raul.dominguez@email.es</td>

                                <td class="vista-escritorio">TotalEnergies</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-inactive">
                                        Inactivo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="22"
                                data-nombre="Elena Prieto"
                                data-iniciales="EP"
                                data-tipo="Particular"
                                data-identificacion="49271835R"
                                data-correo="elena.prieto@email.es"
                                data-comercializadora="Endesa"
                                data-tarifa="Mercado libre"
                                data-tarifaclase="tarifa-libre"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            EP
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Elena Prieto
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">49271835R</td>

                                <td class="vista-escritorio">elena.prieto@email.es</td>

                                <td class="vista-escritorio">Endesa</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-libre">
                                        Mercado libre
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="23"
                                data-nombre="Pablo Ferrer"
                                data-iniciales="PF"
                                data-tipo="Particular"
                                data-identificacion="68392417S"
                                data-correo="pablo.ferrer@email.es"
                                data-comercializadora="Iberdrola"
                                data-tarifa="PVPC"
                                data-tarifaclase="tarifa-pvpc"
                                data-estado="Pendiente"
                                data-estadoclase="cliente-pending"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            PF
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Pablo Ferrer
                                            </strong>

                                            <span>
                                                Particular
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">68392417S</td>

                                <td class="vista-escritorio">pablo.ferrer@email.es</td>

                                <td class="vista-escritorio">Iberdrola</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-pvpc">
                                        PVPC
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-pending">
                                        Pendiente
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <tr class="fila-detalle"
                                data-id="24"
                                data-nombre="Electricidad Centro S.L."
                                data-iniciales="EC"
                                data-tipo="Empresa"
                                data-identificacion="B49281736"
                                data-correo="administracion@electricidadcentro.es"
                                data-comercializadora="Naturgy"
                                data-tarifa="Tarifa fija"
                                data-tarifaclase="tarifa-fija"
                                data-estado="Activo"
                                data-estadoclase="cliente-active"
                            >

                                <td>

                                    <div class="cliente-cell">

                                        <div class="cliente-avatar">
                                            EC
                                        </div>

                                        <div class="cliente-info">

                                            <strong>
                                                Electricidad Centro S.L.
                                            </strong>

                                            <span>
                                                Empresa
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td class="vista-escritorio">B49281736</td>

                                <td class="vista-escritorio">administracion@electricidadcentro.es</td>

                                <td class="vista-escritorio">Naturgy</td>

                                <td class="vista-escritorio">
                                    <span class="tarifa-badge tarifa-fija">
                                        Tarifa fija
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <span class="status-badge cliente-active">
                                        Activo
                                    </span>
                                </td>

                                <td class="vista-escritorio">
                                    <div class="cliente-actions">
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Ver
                                        </button>
                                        <button type="button" class="table-action-button" onclick="event.stopPropagation();">
                                            Editar
                                        </button>
                                    </div>
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


    <!-- =====================================================
         TARJETA DE DETALLE (SOLO MÓVIL)
    ====================================================== -->

    <div id="modalDetalleCliente" class="modal-overlay" style="display: none;">

        <div class="modal-detalle">

            <div class="modal-detalle-header">

                <div class="modal-detalle-avatar" id="detalleClienteAvatar"></div>

                <div class="modal-detalle-titulo">
                    <h2 id="detalleClienteNombre"></h2>
                    <span id="detalleClienteTipo"></span>
                </div>

                <button type="button" class="modal-detalle-close" onclick="cerrarModalDetalleCliente()"
                    aria-label="Cerrar">
                    ✕
                </button>

            </div>

            <div class="usuario-detalle-grid">

                <div class="usuario-detalle-item">
                    <span>Identificación</span>
                    <strong id="detalleClienteIdentificacion"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Correo</span>
                    <strong id="detalleClienteCorreo"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Comercializadora</span>
                    <strong id="detalleClienteComercializadora"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Tarifa</span>
                    <strong id="detalleClienteTarifa"></strong>
                </div>

                <div class="usuario-detalle-item">
                    <span>Estado</span>
                    <strong id="detalleClienteEstado"></strong>
                </div>

            </div>

            <div class="modal-detalle-acciones">

                <button type="button" class="table-action-button danger">
                    Eliminar
                </button>

                <button type="button" class="config-save-button">
                    Editar
                </button>

            </div>

        </div>

    </div>

</body>

</html>
