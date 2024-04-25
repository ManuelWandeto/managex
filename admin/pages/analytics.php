<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="assets/js/chart.js"></script>
<div class="row" x-init="()=>{
    $store.reports.general.getStats()
}">
    <div class="col">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between">
                Download stats
                <span class="loader" x-show="!$store.reports.general.isLoaded" x-transition></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4" x-data>
                        <div class="total">
                        <h3>Total downloads</h3>
                        <span  x-text="$store.reports.general.stats?.total_downloads || 'loading...'"></span>
                        </div>
                        <div class="row mt-4">
                        <div class="col-sm-6 completed">
                            <h6>Paid</h6>
                            <span x-text="$store.reports.general.stats?.paid_downloads || 'loading...'"></span>
                        </div>
                        <div class="col-sm-6 completed">
                            <h6>Free</h6>
                            <span x-text="$store.reports.general.stats?.free_downloads || 'loading...'"></span>
                        </div>
                        </div>
                        <div class="row mt-4">
                        <div class="col-sm-6 completed">
                            <h6>Completed</h6>
                            <span x-text="$store.reports.general.stats?.completed_downloads || 'loading...'"></span>
                        </div>
                        <div class="col-sm-6 completed">
                            <h6>Pending</h6>
                            <span x-text="$store.reports.general.stats?.pending_downloads || 'loading...'">45</span>
                        </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card w-100" x-data="{chart: null, chartCanvas: null}" x-init="()=>{
                Alpine.store('reports').downloadsPerDay.getStats();
            }">
            <div class="card-header d-flex justify-content-between">
                Downloads Per Day
                <span class="loader" x-show="!$store.reports.downloadsPerDay.isLoaded" x-transition></span>
            </div>
            <div class="card-body" x-data x-transition x-show="$store.reports.downloadsPerDay.isLoaded && $store.reports.downloadsPerDay.stats?.length"
            x-init="$watch('$store.reports.downloadsPerDay.stats', (data)=>{
                chartCanvas = document.getElementById('downloads-per-day')
                chart = downloadsPerDayChart(chartCanvas)
            })">
                <canvas id="downloads-per-day"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card w-100" x-data="{chart: null, chartCanvas: null}" x-init="()=>{
                Alpine.store('reports').downloadsPerBusiness.getStats();
            }">
            <div class="card-header d-flex justify-content-between">
                Downloads Per Business Type
                <span class="loader" x-show="!$store.reports.downloadsPerBusiness.isLoaded" x-transition></span>
            </div>
            <div class="card-body" x-data x-transition x-show="$store.reports.downloadsPerBusiness.isLoaded && $store.reports.downloadsPerBusiness.stats?.length"
            x-init="$watch('$store.reports.downloadsPerBusiness.stats', (data)=>{
                chartCanvas = document.getElementById('downloads-per-business')
                chart = downloadsPerBusinessChart(chartCanvas)
            })">
                <canvas id="downloads-per-business"></canvas>
            </div>
        </div>
    </div>
</div>
