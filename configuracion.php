<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Configuración - Comparador Eléctrico</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>


<?php include 'templates/header.php'; ?>


<!-- =========================
     CONTENEDOR PRINCIPAL
========================== -->

<div class="app-container">


    <?php include 'templates/sidebar.php'; ?>


    <!-- =========================
         CONTENIDO
    ========================== -->

    <main class="main-content">


        <div class="page-header">

            <div>
                <h1>Configuración</h1>
                <p>Configuración general del comparador</p>
            </div>

            <button class="config-save-button">
                💾 Guardar cambios
            </button>

        </div>


        <!-- =========================
             GENERAL
        ========================== -->

        <section class="config-card">

            <div class="config-card-header">

                <div class="config-icon">
                    ⚙️
                </div>

                <div>
                    <h2>General</h2>
                    <p>Configuración básica de la aplicación</p>
                </div>

            </div>

            <div class="config-grid">

                <div class="config-field">

                    <label for="app-name">
                        Nombre de la aplicación
                    </label>

                    <input
                        type="text"
                        id="app-name"
                        value="Comparador Eléctrico"
                    >

                </div>


                <div class="config-field">

                    <label for="company-name">
                        Empresa
                    </label>

                    <input
                        type="text"
                        id="company-name"
                        placeholder="Nombre de la empresa"
                    >

                </div>


                <div class="config-field">

                    <label for="language">
                        Idioma
                    </label>

                    <select id="language">

                        <option selected>
                            Español
                        </option>

                        <option>
                            Inglés
                        </option>

                    </select>

                </div>

            </div>

        </section>


        <!-- =========================
             COMPARADOR
        ========================== -->

        <section class="config-card">

            <div class="config-card-header">

                <div class="config-icon">
                    💡
                </div>

                <div>
                    <h2>Comparador</h2>
                    <p>Configuración utilizada para los cálculos</p>
                </div>

            </div>

            <div class="config-grid">

                <div class="config-field">

                    <label for="iva">
                        IVA (%)
                    </label>

                    <input
                        type="number"
                        id="iva"
                        value="21"
                        min="0"
                        step="0.01"
                    >

                </div>


                <div class="config-field">

                    <label for="tax">
                        Impuesto eléctrico (%)
                    </label>

                    <input
                        type="number"
                        id="tax"
                        value="5.11269632"
                        min="0"
                        step="0.0001"
                    >

                </div>


                <div class="config-field">

                    <label for="currency">
                        Moneda
                    </label>

                    <select id="currency">

                        <option selected>
                            Euro (€)
                        </option>

                    </select>

                </div>


                <div class="config-field config-checkbox">

                    <label>

                        <input
                            type="checkbox"
                            checked
                        >

                        Mostrar precios con impuestos

                    </label>

                </div>

            </div>

        </section>


        <!-- =========================
             TARIFAS
        ========================== -->

        <section class="config-card">

            <div class="config-card-header">

                <div class="config-icon">
                    📊
                </div>

                <div>
                    <h2>Tarifas</h2>
                    <p>Configuración de actualización de tarifas</p>
                </div>

            </div>

            <div class="config-grid">

                <div class="config-field">

                    <label for="update-frequency">
                        Actualización de tarifas
                    </label>

                    <select id="update-frequency">

                        <option selected>
                            Automática
                        </option>

                        <option>
                            Manual
                        </option>

                    </select>

                </div>


                <div class="config-field">

                    <label for="update-period">
                        Periodicidad
                    </label>

                    <select id="update-period">

                        <option selected>
                            Diaria
                        </option>

                        <option>
                            Semanal
                        </option>

                        <option>
                            Mensual
                        </option>

                    </select>

                </div>


                <div class="config-field config-checkbox">

                    <label>

                        <input
                            type="checkbox"
                            checked
                        >

                        Activar tarifas

                    </label>

                </div>

            </div>

        </section>


        <!-- =========================
             SISTEMA
        ========================== -->

        <section class="config-card">

            <div class="config-card-header">

                <div class="config-icon">
                    🖥️
                </div>

                <div>
                    <h2>Sistema</h2>
                    <p>Información básica del sistema</p>
                </div>

            </div>

            <div class="system-info">

                <div class="system-item">

                    <span>
                        Versión
                    </span>

                    <strong>
                        1.0.0
                    </strong>

                </div>


                <div class="system-item">

                    <span>
                        Estado
                    </span>

                    <span class="system-status">
                        ● Sistema operativo
                    </span>

                </div>


                <div class="system-item">

                    <span>
                        Última copia de seguridad
                    </span>

                    <strong>
                        No realizada
                    </strong>

                </div>


                <button class="config-secondary-button">
                    💾 Crear copia de seguridad
                </button>

            </div>

        </section>


    </main>

</div>


<?php include 'templates/footer.php'; ?>


</body>

</html>
