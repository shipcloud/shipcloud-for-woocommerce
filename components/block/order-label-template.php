<div id="shipment-{{ data.model.get('id') }}" class="label widget">

    <div class="loading-overlay">
        <div class="spin-loader"></div>
    </div>

    <div class="widget-top">
        <div class="widget-title-action">
            <a class="widget-action hide-if-no-js"></a>
        </div>
        <div class="widget-title">
            <img class="shipcloud-widget-icon" src="<?php echo WCSC_URLPATH; ?>assets/icons/truck-32x32.png"/>

            <h4>
                {{ data.model.get('from').getTitle() }}
                <span class="dashicons dashicons-arrow-right-alt"></span>
                {{ data.model.get('to').getTitle() }}
                <span class="dashicons dashicons-screenoptions"></span>
                {{ data.model.getTitle() }}
            </h4>
        </div>
    </div>

</div>
