@extends('commander::layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div id="activity-app">
        <!-- Header Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-history text-indigo-600 mr-3"></i>
                        Command Activity
                    </h1>
                    <p class="text-gray-600 text-lg">View all command executions and their details</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2">
                        <span class="text-sm text-gray-500">Total Executions:</span>
                        <span class="ml-2 font-semibold text-indigo-600">@{{ pagination.total }}</span>
                    </div>
                    <button @click="refreshActivity" 
                            :disabled="loading"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-sync-alt mr-2" :class="{ 'loading-spinner': loading }"></i>
                        @{{ loading ? 'Loading...' : 'Refresh' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           v-model="filters.command" 
                           placeholder="Search commands..." 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                <div class="relative">
                    <i class="fas fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <select v-model="filters.status" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
                <div>
                    <input type="date" 
                           v-model="filters.dateFrom" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="From Date">
                </div>
                <div>
                    <input type="date" 
                           v-model="filters.dateTo" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="To Date">
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <button @click="applyFilters" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>
                    Apply Filters
                </button>
                <button @click="clearFilters" 
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="inline-block">
                <div class="loading-spinner rounded-full h-12 w-12 border-4 border-indigo-200 border-t-indigo-600"></div>
                <p class="mt-4 text-gray-600 text-lg">Loading activity...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-semibold">Error Loading Activity</h3>
                    <p class="text-red-600 mt-1">@{{ error }}</p>
                </div>
            </div>
            <button @click="refreshActivity" class="mt-4 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                Try Again
            </button>
        </div>

        <!-- Activity Table -->
        <div v-if="!loading && !error" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Command
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Duration
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Started
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="execution in activity" :key="execution.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">@{{ execution.command_name }}</div>
                                <div class="text-sm text-gray-500">@{{ execution.environment }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="execution.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                      class="px-2 py-1 text-xs rounded-full font-medium">
                                    @{{ execution.success ? 'Success' : 'Failed' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @{{ execution.execution_time }}s
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @{{ formatDate(execution.started_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @{{ execution.user?.name || 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="showExecutionDetails(execution)" 
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button @click="retryExecution(execution)" 
                                        v-if="!execution.success"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="changePage(pagination.current_page - 1)" 
                            :disabled="pagination.current_page === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        Previous
                    </button>
                    <button @click="changePage(pagination.current_page + 1)" 
                            :disabled="pagination.current_page === pagination.last_page"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">@{{ pagination.from }}</span> to <span class="font-medium">@{{ pagination.to }}</span> of <span class="font-medium">@{{ pagination.total }}</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button @click="changePage(pagination.current_page - 1)" 
                                    :disabled="pagination.current_page === 1"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button v-for="page in getPageNumbers()" 
                                    :key="page"
                                    @click="changePage(page)"
                                    :class="page === pagination.current_page ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                @{{ page }}
                            </button>
                            <button @click="changePage(pagination.current_page + 1)" 
                                    :disabled="pagination.current_page === pagination.last_page"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && activity.length === 0" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-history text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No activity found</h3>
            <p class="text-gray-600">No command executions match your current filters.</p>
        </div>

        <!-- Execution Details Modal -->
        <div v-if="selectedExecution" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
             @click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-terminal text-indigo-600 mr-2"></i>
                            Execution Details
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Command Information</h4>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Command:</span>
                                        <span class="font-medium">@{{ selectedExecution.command_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Status:</span>
                                        <span :class="selectedExecution.success ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                            @{{ selectedExecution.success ? 'Success' : 'Failed' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Duration:</span>
                                        <span class="font-medium">@{{ selectedExecution.execution_time }}s</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Return Code:</span>
                                        <span class="font-medium">@{{ selectedExecution.return_code }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Timing</h4>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Started:</span>
                                        <span class="font-medium">@{{ formatDate(selectedExecution.started_at) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Completed:</span>
                                        <span class="font-medium">@{{ formatDate(selectedExecution.completed_at) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Environment:</span>
                                        <span class="font-medium">@{{ selectedExecution.environment }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">User:</span>
                                        <span class="font-medium">@{{ selectedExecution.user?.name || 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Arguments and Options -->
                        <div v-if="selectedExecution.arguments && Object.keys(selectedExecution.arguments).length > 0">
                            <h4 class="font-semibold text-gray-900 mb-2">Arguments</h4>
                            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">@{{ JSON.stringify(selectedExecution.arguments, null, 2) }}</pre>
                        </div>

                        <div v-if="selectedExecution.options && Object.keys(selectedExecution.options).length > 0">
                            <h4 class="font-semibold text-gray-900 mb-2">Options</h4>
                            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">@{{ JSON.stringify(selectedExecution.options, null, 2) }}</pre>
                        </div>

                        <!-- Output -->
                        <div v-if="selectedExecution.output">
                            <h4 class="font-semibold text-gray-900 mb-2">Output</h4>
                            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto max-h-96">@{{ selectedExecution.output }}</pre>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-3">
                            <button @click="retryExecution(selectedExecution)" 
                                    v-if="!selectedExecution.success"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                <i class="fas fa-redo mr-2"></i>
                                Retry Command
                            </button>
                            <button @click="closeModal" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
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
            activity: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 20,
                total: 0,
                from: 0,
                to: 0
            },
            filters: {
                command: '',
                status: '',
                dateFrom: '',
                dateTo: ''
            },
            loading: false,
            error: null,
            selectedExecution: null
        }
    },
    mounted() {
        this.loadActivity();
    },
    methods: {
        async loadActivity() {
            this.loading = true;
            this.error = null;
            
            try {
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    ...this.filters
                });
                
                const response = await fetch(`{{ commander_route("api.activity") }}?${params}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                this.activity = data.data;
                this.pagination = data.pagination;
            } catch (error) {
                this.error = 'Failed to load activity. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        async retryExecution(execution) {
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
                        command: execution.command_name
                    })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Refresh the activity list
                    this.loadActivity();
                    this.closeModal();
                } else {
                    alert('Failed to retry command: ' + result.message);
                }
            } catch (error) {
                alert('Failed to retry command: ' + error.message);
            }
        },
        
        showExecutionDetails(execution) {
            this.selectedExecution = execution;
        },
        
        closeModal() {
            this.selectedExecution = null;
        },
        
        applyFilters() {
            this.pagination.current_page = 1;
            this.loadActivity();
        },
        
        clearFilters() {
            this.filters = {
                command: '',
                status: '',
                dateFrom: '',
                dateTo: ''
            };
            this.pagination.current_page = 1;
            this.loadActivity();
        },
        
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.pagination.current_page = page;
                this.loadActivity();
            }
        },
        
        getPageNumbers() {
            const pages = [];
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            
            if (last <= 7) {
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 4) {
                    for (let i = 1; i <= 5; i++) {
                        pages.push(i);
                    }
                    pages.push('...');
                    pages.push(last);
                } else if (current >= last - 3) {
                    pages.push(1);
                    pages.push('...');
                    for (let i = last - 4; i <= last; i++) {
                        pages.push(i);
                    }
                } else {
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) {
                        pages.push(i);
                    }
                    pages.push('...');
                    pages.push(last);
                }
            }
            
            return pages;
        },
        
        refreshActivity() {
            this.loadActivity();
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString();
        }
    }
}).mount('#activity-app');
</script>
@endsection 