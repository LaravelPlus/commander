@extends('commander::layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div id="dashboard-app">
        <!-- Header Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-tachometer-alt text-indigo-600 mr-3"></i>
                        Command Dashboard
                    </h1>
                    <p class="text-gray-600 text-lg">Monitor and manage your Laravel Artisan commands</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2">
                        <span class="text-sm text-gray-500">Total Commands:</span>
                        <span class="ml-2 font-semibold text-indigo-600">@{{ stats.totalCommands }}</span>
                    </div>
                    <button @click="refreshDashboard" 
                            :disabled="loading"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-sync-alt mr-2" :class="{ 'loading-spinner': loading }"></i>
                        @{{ loading ? 'Loading...' : 'Refresh' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6">
                <div class="flex items-center">
                    <i class="fas fa-terminal text-3xl mr-4"></i>
                    <div>
                        <div class="text-3xl font-bold">@{{ stats.totalCommands }}</div>
                        <div class="text-blue-100">Total Commands</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-3xl mr-4"></i>
                    <div>
                        <div class="text-3xl font-bold">@{{ stats.successfulExecutions }}</div>
                        <div class="text-green-100">Successful</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl p-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-3xl mr-4"></i>
                    <div>
                        <div class="text-3xl font-bold">@{{ stats.failedExecutions }}</div>
                        <div class="text-red-100">Failed</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl p-6">
                <div class="flex items-center">
                    <i class="fas fa-clock text-3xl mr-4"></i>
                    <div>
                        <div class="text-3xl font-bold">@{{ stats.scheduledCommands }}</div>
                        <div class="text-purple-100">Scheduled</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activity -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-history text-indigo-600 mr-2"></i>
                            Recent Activity
                        </h2>
                        <a href="{{ commander_url('list') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            View All →
                        </a>
                    </div>
                    
                    <div v-if="loading" class="text-center py-8">
                        <div class="loading-spinner rounded-full h-8 w-8 border-4 border-indigo-200 border-t-indigo-600 mx-auto"></div>
                        <p class="mt-2 text-gray-600">Loading recent activity...</p>
                    </div>
                    
                    <div v-else-if="recentExecutions.length === 0" class="text-center py-8">
                        <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">No recent executions</p>
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div v-for="execution in recentExecutions" :key="execution.id" 
                             class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div :class="execution.success ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'" 
                                     class="p-2 rounded-full">
                                    <i :class="execution.success ? 'fas fa-check' : 'fas fa-times'"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">@{{ execution.command_name }}</div>
                                    <div class="text-sm text-gray-500">@{{ formatDate(execution.started_at) }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">@{{ execution.execution_time }}s</div>
                                <div class="text-xs text-gray-500">@{{ execution.environment }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-bolt text-indigo-600 mr-2"></i>
                        Quick Actions
                    </h2>
                    <div class="space-y-3">
                        <a href="{{ commander_url('list') }}" 
                           class="flex items-center p-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                            <i class="fas fa-list text-indigo-600 mr-3"></i>
                            <span class="font-medium text-gray-900">View All Commands</span>
                        </a>
                        <a href="{{ commander_url('schedule') }}" 
                           class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                            <i class="fas fa-clock text-purple-600 mr-3"></i>
                            <span class="font-medium text-gray-900">Scheduled Commands</span>
                        </a>
                        <button @click="runQuickCommand('cache:clear')" 
                                :disabled="quickCommands.running"
                                class="w-full flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors disabled:opacity-50">
                            <i class="fas fa-broom text-green-600 mr-3"></i>
                            <span class="font-medium text-gray-900">Clear Cache</span>
                        </button>
                        <button @click="runQuickCommand('config:cache')" 
                                :disabled="quickCommands.running"
                                class="w-full flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors disabled:opacity-50">
                            <i class="fas fa-cog text-blue-600 mr-3"></i>
                            <span class="font-medium text-gray-900">Cache Config</span>
                        </button>
                    </div>
                </div>

                <!-- System Status -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-server text-indigo-600 mr-2"></i>
                        System Status
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Environment</span>
                            <span class="font-medium text-gray-900">@{{ systemStatus.environment }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Debug Mode</span>
                            <span :class="systemStatus.debug ? 'text-red-600' : 'text-green-600'" class="font-medium">
                                @{{ systemStatus.debug ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Queue Status</span>
                            <span :class="systemStatus.queueRunning ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                @{{ systemStatus.queueRunning ? 'Running' : 'Stopped' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Storage</span>
                            <span class="font-medium text-gray-900">@{{ systemStatus.storageFree }}</span>
                        </div>
                    </div>
                </div>

                <!-- Popular Commands -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-star text-indigo-600 mr-2"></i>
                        Popular Commands
                    </h2>
                    <div class="space-y-3">
                        <div v-for="command in popularCommands" :key="command.name" 
                             class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg cursor-pointer"
                             @click="runQuickCommand(command.name)">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-play text-gray-400 text-sm"></i>
                                <span class="text-sm font-medium text-gray-900">@{{ command.name }}</span>
                            </div>
                            <span class="text-xs text-gray-500">@{{ command.execution_count }}x</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Command Result Modal -->
        <div v-if="quickCommandResult" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
             @click.self="closeQuickCommandModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-terminal text-indigo-600 mr-2"></i>
                            Command Result
                        </h3>
                        <button @click="closeQuickCommandModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">Command:</span>
                            <span class="text-gray-600">@{{ quickCommandResult.command }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">Status:</span>
                            <span :class="quickCommandResult.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                  class="px-2 py-1 text-xs rounded-full font-medium">
                                @{{ quickCommandResult.success ? 'Success' : 'Failed' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">Duration:</span>
                            <span class="text-gray-600">@{{ quickCommandResult.execution_time }}s</span>
                        </div>
                        
                        <div v-if="quickCommandResult.output" class="mt-4">
                            <h4 class="font-medium text-gray-900 mb-2">Output:</h4>
                            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">@{{ quickCommandResult.output }}</pre>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button @click="closeQuickCommandModal" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            loading: false,
            stats: {
                totalCommands: 0,
                successfulExecutions: 0,
                failedExecutions: 0,
                scheduledCommands: 0
            },
            recentExecutions: [],
            popularCommands: [],
            systemStatus: {
                environment: 'local',
                debug: true,
                queueRunning: true,
                storageFree: '2.5 GB'
            },
            quickCommands: {
                running: false
            },
            quickCommandResult: null
        }
    },
    mounted() {
        this.loadDashboard();
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            
            try {
                // Load stats
                const statsResponse = await fetch('{{ commander_route("api.dashboard") }}');
                if (statsResponse.ok) {
                    const statsData = await statsResponse.json();
                    this.stats = {
                        totalCommands: statsData.total_commands || 0,
                        successfulExecutions: statsData.successful_executions || 0,
                        failedExecutions: statsData.failed_executions || 0,
                        scheduledCommands: statsData.scheduled_commands || 0
                    };
                }
                
                // Load recent executions
                const recentResponse = await fetch('{{ commander_route("api.recent") }}');
                if (recentResponse.ok) {
                    this.recentExecutions = await recentResponse.json();
                }
                
                // Load popular commands
                const popularResponse = await fetch('{{ commander_route("api.popular") }}');
                if (popularResponse.ok) {
                    this.popularCommands = await popularResponse.json();
                }
                
            } catch (error) {
                // Error handling without console logging
            } finally {
                this.loading = false;
            }
        },
        
        async runQuickCommand(commandName) {
            if (this.quickCommands.running) return;
            
            this.quickCommands.running = true;
            
            try {
                const response = await fetch('{{ commander_route("api.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        command: commandName,
                        arguments: {},
                        options: {}
                    })
                });
                
                const result = await response.json();
                this.quickCommandResult = {
                    command: commandName,
                    success: result.success,
                    execution_time: result.execution_time,
                    output: result.output
                };
                
                // Refresh dashboard after command execution
                this.loadDashboard();
                
            } catch (error) {
                this.quickCommandResult = {
                    command: commandName,
                    success: false,
                    execution_time: 0,
                    output: 'Failed to execute command: ' + error.message
                };
            } finally {
                this.quickCommands.running = false;
            }
        },
        
        closeQuickCommandModal() {
            this.quickCommandResult = null;
        },
        
        refreshDashboard() {
            this.loadDashboard();
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString();
        }
    }
}).mount('#dashboard-app');
</script>
@endsection 