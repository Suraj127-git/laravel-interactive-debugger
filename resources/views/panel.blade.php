<div x-data="debuggerPanel()" 
     class="fixed {{ config('interactive-debugger.panel_position') }} m-4 p-4 bg-gray-800 text-white rounded shadow-lg z-50" 
     style="width: 300px; height: 400px; overflow-y: auto;">
    <div class="flex justify-between items-center mb-2">
        <h4 class="text-lg font-bold">Debugger Panel</h4>
        <button @click="togglePanel()" class="text-sm">Minimize</button>
    </div>
    <template x-if="visible">
        <div>
            <h5 class="mt-2 font-semibold">Queries</h5>
            <ul>
                <template x-for="query in data.queries" :key="query.sql">
                    <li class="text-xs" x-text="query.sql"></li>
                </template>
            </ul>
            <h5 class="mt-2 font-semibold">Logs</h5>
            <ul>
                <template x-for="log in data.logs" :key="log.message">
                    <li class="text-xs" x-text="log.level + ': ' + log.message"></li>
                </template>
            </ul>
        </div>
    </template>
</div>

<script>
    function debuggerPanel() {
        return {
            visible: true,
            data: { queries: [], logs: [] },
            togglePanel() { this.visible = !this.visible; },
            fetchData() {
                fetch('/__debugger_api')
                  .then(response => response.json())
                  .then(json => { this.data = json; });
            },
            init() {
                this.fetchData();
                setInterval(this.fetchData, {{ config('interactive-debugger.update_interval', 3000) }});
            }
        }
    }
</script>
