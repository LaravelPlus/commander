@extends('commander::layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div id="retry-app">
        <!-- Header Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-redo text-red-600 mr-3"></i>
                        Retry Failed Commands
                    </h1>
                    <p class="text-gray-600 text-lg">Retry commands that have failed execution</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2">
                        <span class="text-sm text-gray-500">Failed Commands:</span>
                        <span class="ml-2 font-semibold text-red-600">@{{ failedCommands.length }}</span>
                    </div>
                    <button @click="refreshFailedCommands" 
                            :disabled="loading"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-sync-alt mr-2" :class="{ 'loading-spinner': loading }"></i>
                        @{{ loading ? 'Loading...' : 'Refresh' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           v-model="searchQuery" 
                           placeholder="Search failed commands..." 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                </div>
                <div class="relative">
                    <i class="fas fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <select v-model="selectedFailureCount" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none bg-white">
                        <option value="">All Failure Counts</option>
                        <option value="1">1 failure</option>
                        <option value="2">2+ failures</option>
                        <option value="5">5+ failures</option>
                        <option value="10">10+ failures</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
                <div class="flex space-x-2">
                    <button @click="retryAllFailed" 
                            :disabled="loading || failedCommands.length === 0"
                            class="flex-1 bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 transition-colors disabled:opacity-50">
                        <i class="fas fa-redo mr-2"></i>
                        Retry All
                    </button>
                    <button @click="clearFailedRecords" 
                            :disabled="loading"
                            class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        Clear Records
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="inline-block">
                <div class="loading-spinner rounded-full h-12 w-12 border-4 border-red-200 border-t-red-600"></div>
                <p class="mt-4 text-gray-600 text-lg">Loading failed commands...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-semibold">Error Loading Failed Commands</h3>
                    <p class="text-red-600 mt-1">@{{ error }}</p>
                </div>
            </div>
            <button @click="refreshFailedCommands" class="mt-4 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                Try Again
            </button>
        </div>

        <!-- Failed Commands Grid -->
        <div v-if="!loading && !error" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="command in filteredFailedCommands" 
                 :key="command.command_name"
                 class="command-card bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all">
                
                <!-- Command Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate" :title="command.command_name">
                            @{{ command.command_name }}
                        </h3>
                        <p class="text-sm text-gray-600">Failed @{{ command.failure_count }} time(s)</p>
                    </div>
                    <div class="flex space-x-2 ml-2">
                        <button @click="showCommandDetails(command)" 
                                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                title="View Details">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button @click="retryCommand(command)"
                                :disabled="command.retrying"
                                class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50"
                                :title="command.retrying ? 'Retrying...' : 'Retry Command'">
                            <i :class="command.retrying ? 'fas fa-spinner loading-spinner' : 'fas fa-redo'"></i>
                        </button>
                    </div>
                </div>

                <!-- Failure Information -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Last Failed:</span>
                        <span class="text-gray-700">@{{ formatDate(command.last_failed_at) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Failure Count:</span>
                        <span class="text-red-600 font-medium">@{{ command.failure_count }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Status:</span>
                        <span class="bg-red-100 text-red-800 px-2 py-1 text-xs rounded-full font-medium">
                            Failed
                        </span>
                    </div>
                </div>

                <!-- Retry Result -->
                <div v-if="command.retryResult" class="mt-4 p-3 rounded-lg" 
                     :class="command.retryResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium" :class="command.retryResult.success ? 'text-green-800' : 'text-red-800'">
                            @{{ command.retryResult.success ? 'Retry Successful' : 'Retry Failed' }}
                        </span>
                        <span class="text-xs" :class="command.retryResult.success ? 'text-green-600' : 'text-red-600'">
                            @{{ command.retryResult.execution_time }}s
                        </span>
                    </div>
                    <div v-if="command.retryResult.output" class="mt-2 text-xs text-gray-600 truncate">
                        @{{ command.retryResult.output }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && filteredFailedCommands.length === 0" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-check-circle text-green-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No failed commands</h3>
            <p class="text-gray-600">All commands are running successfully!</p>
        </div>

        <!-- Command Details Modal -->
        <div v-if="selectedCommand" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
             @click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                            Failed Command Details
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Command Info -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Command Information</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Command:</span>
                                        <span class="ml-2 font-medium text-gray-900">@{{ selectedCommand.command_name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Failure Count:</span>
                                        <span class="ml-2 font-medium text-red-600">@{{ selectedCommand.failure_count }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Last Failed:</span>
                                        <span class="ml-2 text-gray-900">@{{ formatDate(selectedCommand.last_failed_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Details -->
                        <div v-if="selectedCommand.lastError">
                            <h4 class="font-semibold text-gray-900 mb-2">Last Error</h4>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <pre class="text-sm text-red-800 overflow-x-auto">@{{ selectedCommand.lastError }}</pre>
                            </div>
                        </div>

                        <!-- Retry Actions -->
                        <div class="flex space-x-3">
                            <button @click="retryCommand(selectedCommand)"
                                    :disabled="selectedCommand.retrying"
                                    class="flex-1 bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 transition-colors disabled:opacity-50">
                                <i :class="selectedCommand.retrying ? 'fas fa-spinner loading-spinner' : 'fas fa-redo'" class="mr-2"></i>
                                @{{ selectedCommand.retrying ? 'Retrying...' : 'Retry Command' }}
                            </button>
                            <button @click="closeModal" class="px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Close
                            </button>
                        </div>
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
            failedCommands: [],
            loading: false,
            error: null,
            searchQuery: '',
            selectedFailureCount: '',
            selectedCommand: null
        }
    },
    computed: {
        filteredFailedCommands() {
            return this.failedCommands.filter(command => {
                const matchesSearch = command.command_name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesFailureCount = !this.selectedFailureCount || 
                    (this.selectedFailureCount === '1' && command.failure_count === 1) ||
                    (this.selectedFailureCount === '2' && command.failure_count >= 2) ||
                    (this.selectedFailureCount === '5' && command.failure_count >= 5) ||
                    (this.selectedFailureCount === '10' && command.failure_count >= 10);
                return matchesSearch && matchesFailureCount;
            });
        }
    },
    mounted() {
        this.loadFailedCommands();
    },
    methods: {
        async loadFailedCommands() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch('{{ commander_route("api.failed") }}');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                this.failedCommands = data.map(cmd => ({
                    ...cmd,
                    retrying: false,
                    retryResult: null
                }));
            } catch (error) {
                this.error = 'Failed to load failed commands. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        async retryCommand(command) {
            if (command.retrying) return;
            
            command.retrying = true;
            command.retryResult = null;
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }
                
                const response = await fetch('{{ commander_route("api.retry") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        command: command.command_name
                    })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
                }
                
                const result = await response.json();
                
                command.retryResult = {
                    success: result.success,
                    execution_time: result.execution_time,
                    output: result.output
                };
                
                // If retry was successful, remove from failed list after a delay
                if (result.success) {
                    setTimeout(() => {
                        this.failedCommands = this.failedCommands.filter(c => c.command_name !== command.command_name);
                    }, 3000);
                }
                
            } catch (error) {
                command.retryResult = {
                    success: false,
                    execution_time: 0,
                    output: 'Failed to retry command: ' + error.message
                };
            } finally {
                command.retrying = false;
            }
        },
        
        async retryAllFailed() {
            for (const command of this.filteredFailedCommands) {
                await this.retryCommand(command);
                // Small delay between retries
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        },
        
        async clearFailedRecords() {
            if (!confirm('Are you sure you want to clear all failed command records? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch('{{ commander_route("api.cleanup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        failed_only: true
                    })
                });
                
                if (response.ok) {
                    this.loadFailedCommands();
                }
            } catch (error) {
                // Error handling without console logging
            }
        },
        
        showCommandDetails(command) {
            this.selectedCommand = command;
        },
        
        closeModal() {
            this.selectedCommand = null;
        },
        
        refreshFailedCommands() {
            this.loadFailedCommands();
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString();
        }
    }
}).mount('#retry-app');
</script>
@endsection 