@extends('commander::layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div id="schedule-app">
        <!-- Header Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-clock text-indigo-600 mr-3"></i>
                        Scheduled Commands
                    </h1>
                    <p class="text-gray-600 text-lg">View and manage all scheduled Laravel Artisan commands</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-2">
                        <span class="text-sm text-gray-500">Scheduled:</span>
                        <span class="ml-2 font-semibold text-indigo-600">@{{ scheduledCommands.length }}</span>
                    </div>
                    <button @click="refreshSchedule" 
                            :disabled="loading"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                           placeholder="Search scheduled commands..." 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                <div class="relative">
                    <i class="fas fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <select v-model="selectedFrequency" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                        <option value="">All Frequencies</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="hourly">Hourly</option>
                        <option value="everyMinute">Every Minute</option>
                        <option value="custom">Custom</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
                <div class="flex space-x-2">
                    <button @click="showNextRun = !showNextRun" 
                            class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 transition-colors">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Next Run
                    </button>
                    <button @click="showLastRun = !showLastRun" 
                            class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 transition-colors">
                        <i class="fas fa-history mr-2"></i>
                        Last Run
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="inline-block">
                <div class="loading-spinner rounded-full h-12 w-12 border-4 border-indigo-200 border-t-indigo-600"></div>
                <p class="mt-4 text-gray-600 text-lg">Loading scheduled commands...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-semibold">Error Loading Scheduled Commands</h3>
                    <p class="text-red-600 mt-1">@{{ error }}</p>
                </div>
            </div>
            <button @click="refreshSchedule" class="mt-4 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                Try Again
            </button>
        </div>

        <!-- Scheduled Commands Grid -->
        <div v-if="!loading && !error" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="command in filteredScheduledCommands" 
                 :key="command.name"
                 class="command-card bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all">
                
                <!-- Command Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate" :title="command.name">
                            @{{ command.name }}
                        </h3>
                        <p class="text-sm text-gray-600 line-clamp-2" :title="command.description">
                            @{{ command.description }}
                        </p>
                    </div>
                    <div class="flex space-x-2 ml-2">
                        <button @click="showCommandDetails(command)" 
                                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                title="View Details">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button @click="runCommand(command)"
                                :disabled="command.running"
                                class="p-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors disabled:opacity-50"
                                :title="command.running ? 'Running...' : 'Run Command'">
                            <i :class="command.running ? 'fas fa-spinner loading-spinner' : 'fas fa-play'"></i>
                        </button>
                    </div>
                </div>

                <!-- Schedule Information -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Schedule:</span>
                        <span class="text-gray-700 font-medium">@{{ command.schedule }}</span>
                    </div>
                    <div v-if="showNextRun" class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Next Run:</span>
                        <span class="text-gray-700">@{{ formatNextRun(command.next_run) }}</span>
                    </div>
                    <div v-if="showLastRun && command.last_run" class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Last Run:</span>
                        <span class="text-gray-700">@{{ formatDate(command.last_run) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Status:</span>
                        <span :class="command.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                              class="px-2 py-1 text-xs rounded-full font-medium">
                            @{{ command.enabled ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && !error && filteredScheduledCommands.length === 0" class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-clock text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No scheduled commands found</h3>
            <p class="text-gray-600">No commands are currently scheduled to run automatically.</p>
        </div>

        <!-- Command Arguments Modal -->
        <div v-if="selectedCommandForExecution" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-start justify-center p-4 z-50 overflow-y-auto"
             @click.self="closeCommandModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full my-8 relative">
                <!-- Sticky Header -->
                <div class="sticky top-0 bg-white rounded-t-xl border-b border-gray-200 px-6 py-4 z-10">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-play-circle text-indigo-600 mr-2"></i>
                            Confirm Command Execution
                        </h3>
                        <button @click="closeCommandModal" 
                                class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Command Form -->
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Command Info -->
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4 border border-indigo-100">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                <i class="fas fa-terminal text-indigo-600 mr-2"></i>
                                @{{ selectedCommandForExecution.name }}
                            </h4>
                            <p class="text-gray-600 text-sm">@{{ selectedCommandForExecution.description }}</p>
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                    <span class="text-yellow-800 text-sm font-medium">
                                        Are you sure you want to execute this command? This action cannot be undone.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Arguments Section -->
                        <div v-if="selectedCommandForExecution.arguments && selectedCommandForExecution.arguments.length > 0">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-list text-indigo-600 mr-2"></i>
                                Arguments
                            </h4>
                            <div class="space-y-3">
                                <div v-for="arg in selectedCommandForExecution.arguments" :key="arg.name" 
                                     class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <label :for="'arg-' + arg.name" class="font-medium text-gray-900">
                                            @{{ arg.name }}
                                            <span v-if="arg.required" class="text-red-500 ml-1">*</span>
                                        </label>
                                        <span v-if="arg.default" class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            Default: @{{ arg.default }}
                                        </span>
                                    </div>
                                    <input :id="'arg-' + arg.name"
                                           v-model="commandArguments[arg.name]"
                                           :placeholder="arg.default || 'Enter value'"
                                           :required="arg.required"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Options Section -->
                        <div v-if="selectedCommandForExecution.options && selectedCommandForExecution.options.length > 0">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-cog text-indigo-600 mr-2"></i>
                                Options
                            </h4>
                            <div class="space-y-3">
                                <div v-for="option in selectedCommandForExecution.options" :key="option.name" 
                                     class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <label :for="'opt-' + option.name" class="font-medium text-gray-900">
                                            @{{ option.name }}
                                            <span v-if="option.required" class="text-red-500 ml-1">*</span>
                                        </label>
                                        <span v-if="option.shortcut" class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            -@{{ option.shortcut }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <input :id="'opt-' + option.name"
                                               v-model="commandOptions[option.name]"
                                               :placeholder="option.default || 'Enter value'"
                                               :required="option.required"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   v-model="commandOptions[option.name + '_enabled']"
                                                   class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-600">Enable</span>
                                        </label>
                                    </div>
                                    <p v-if="option.description" class="text-xs text-gray-500 mt-1">@{{ option.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button @click="closeCommandModal" 
                                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button @click="executeCommandWithArgs" 
                                    :disabled="executing"
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-play mr-2"></i>
                                @{{ executing ? 'Executing...' : 'Yes, Execute Command' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Execution Results Modal -->
        <div v-if="executionResult" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-start justify-center p-4 z-50 overflow-y-auto"
             @click.self="closeExecutionModal">
            <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full my-8 relative">
                <!-- Sticky Header -->
                <div class="sticky top-0 bg-white rounded-t-xl border-b border-gray-200 px-6 py-4 z-10">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-terminal text-indigo-600 mr-2"></i>
                            Execution Results
                        </h3>
                        <button @click="closeExecutionModal" 
                                class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Scrollable Content -->
                <div class="p-6 max-h-[calc(100vh-8rem)] overflow-y-auto">
                    <div class="space-y-6">
                        <!-- Command Info -->
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-6 border border-indigo-100">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-terminal text-indigo-600 mr-2"></i>
                                    @{{ executionResult.command }}
                                </h4>
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <span class="text-gray-500 text-sm mr-2">Status:</span>
                                        <span :class="executionResult.success ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200'" 
                                              class="px-3 py-1 text-xs rounded-full border font-medium">
                                            <i :class="executionResult.success ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'" class="mr-1"></i>
                                            @{{ executionResult.success ? 'Success' : 'Failed' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="text-gray-500 text-sm mr-2">Duration:</span>
                                        <span class="text-gray-900 font-medium">@{{ executionResult.execution_time }}s</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Output Section -->
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-800 text-gray-200 px-4 py-3 border-b border-gray-700">
                                <h4 class="font-semibold flex items-center">
                                    <i class="fas fa-terminal text-green-400 mr-2"></i>
                                    Output
                                    <span class="ml-auto text-xs text-gray-400">@{{ executionResult.output ? executionResult.output.length + ' characters' : 'No output' }}</span>
                                </h4>
                            </div>
                            <div class="bg-gray-900 text-green-400 p-4 font-mono text-sm overflow-x-auto">
                                <pre class="whitespace-pre-wrap break-words">@{{ executionResult.output || 'No output' }}</pre>
                            </div>
                        </div>

                        <!-- Error Section (if any) -->
                        <div v-if="executionResult.error" class="bg-white border border-red-200 rounded-lg overflow-hidden">
                            <div class="bg-red-50 text-red-800 px-4 py-3 border-b border-red-200">
                                <h4 class="font-semibold flex items-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                    Error Details
                                </h4>
                            </div>
                            <div class="bg-red-50 border-red-200 p-4 text-red-800">
                                <pre class="whitespace-pre-wrap break-words">@{{ executionResult.error }}</pre>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button @click="closeExecutionModal" 
                                    class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                                <i class="fas fa-times mr-2"></i>
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
            scheduledCommands: [],
            loading: false,
            error: null,
            searchQuery: '',
            selectedFrequency: '',
            showNextRun: true,
            showLastRun: true,
            selectedCommand: null,
            selectedCommandForExecution: null,
            executionResult: null,
            executing: false,
            commandArguments: {},
            commandOptions: {}
        }
    },
    computed: {
        filteredScheduledCommands() {
            return this.scheduledCommands.filter(command => {
                const matchesSearch = command.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    command.description.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesFrequency = !this.selectedFrequency || command.frequency === this.selectedFrequency;
                return matchesSearch && matchesFrequency;
            });
        }
    },
    mounted() {
        this.loadScheduledCommands();
    },
    methods: {
        async loadScheduledCommands() {
            this.loading = true;
            this.error = null;
            
            try {
                // For now, we'll simulate scheduled commands
                // In a real implementation, this would fetch from the scheduler
                this.scheduledCommands = [
                    {
                        name: 'schedule:run',
                        description: 'Run the scheduled commands',
                        schedule: 'Every minute',
                        frequency: 'everyMinute',
                        next_run: new Date(Date.now() + 60000).toISOString(),
                        last_run: new Date(Date.now() - 60000).toISOString(),
                        enabled: true,
                        running: false
                    },
                    {
                        name: 'backup:run',
                        description: 'Create a backup of the application',
                        schedule: 'Daily at 2:00 AM',
                        frequency: 'daily',
                        next_run: new Date(Date.now() + 86400000).toISOString(),
                        last_run: new Date(Date.now() - 86400000).toISOString(),
                        enabled: true,
                        running: false
                    },
                    {
                        name: 'queue:work',
                        description: 'Process queue jobs',
                        schedule: 'Every 5 minutes',
                        frequency: 'custom',
                        next_run: new Date(Date.now() + 300000).toISOString(),
                        last_run: new Date(Date.now() - 300000).toISOString(),
                        enabled: true,
                        running: false
                    }
                ];
            } catch (error) {
                this.error = 'Failed to load scheduled commands. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        async runCommand(command) {
            if (command.running) return;
            
            // Check if command is disabled
            if (command.disabled) {
                alert('This command is disabled and cannot be executed.');
                return;
            }
            
            // Always show confirmation modal first
            this.selectedCommandForExecution = command;
            this.resetCommandForm();
        },
        
        resetCommandForm() {
            this.commandArguments = {};
            this.commandOptions = {};
            // Set default values
            if (this.selectedCommandForExecution) {
                if (this.selectedCommandForExecution.arguments) {
                    this.selectedCommandForExecution.arguments.forEach(arg => {
                        if (arg.default) {
                            this.commandArguments[arg.name] = arg.default;
                        }
                    });
                }
                if (this.selectedCommandForExecution.options) {
                    this.selectedCommandForExecution.options.forEach(option => {
                        if (option.default) {
                            this.commandOptions[option.name] = option.default;
                        }
                    });
                }
            }
        },
        
        async executeCommandWithArgs() {
            if (!this.selectedCommandForExecution) return;
            
            this.executing = true;
            
            try {
                // Prepare arguments and options
                const arguments = {};
                const options = {};
                
                // Process arguments
                if (this.selectedCommandForExecution.arguments) {
                    this.selectedCommandForExecution.arguments.forEach(arg => {
                        if (this.commandArguments[arg.name] !== undefined && this.commandArguments[arg.name] !== '') {
                            arguments[arg.name] = this.commandArguments[arg.name];
                        }
                    });
                }
                
                // Process options
                if (this.selectedCommandForExecution.options) {
                    this.selectedCommandForExecution.options.forEach(option => {
                        const optionValue = this.commandOptions[option.name];
                        const optionEnabled = this.commandOptions[option.name + '_enabled'];
                        
                        if (optionEnabled) {
                            if (optionValue !== undefined && optionValue !== '') {
                                options[option.name] = optionValue;
                            } else {
                                options[option.name] = true; // Flag option
                            }
                        }
                    });
                }
                
                const response = await fetch('{{ commander_route("api.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        command: this.selectedCommandForExecution.name,
                        arguments: arguments,
                        options: options
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.executionResult = result;
                    // Update the command's last run
                    this.selectedCommandForExecution.last_run = new Date().toISOString();
                } else {
                    this.executionResult = {
                        command: this.selectedCommandForExecution.name,
                        success: false,
                        error: result.message || 'Command execution failed',
                        output: result.output || ''
                    };
                }
                
                // Close the arguments modal
                this.closeCommandModal();
                
            } catch (error) {
                this.executionResult = {
                    command: this.selectedCommandForExecution.name,
                    success: false,
                    error: 'Failed to execute command: ' + error.message,
                    output: ''
                };
                this.closeCommandModal();
            } finally {
                this.executing = false;
            }
        },
        showCommandDetails(command) {
            this.selectedCommand = command;
        },
        closeModal() {
            this.selectedCommand = null;
        },
        closeCommandModal() {
            this.selectedCommandForExecution = null;
            this.commandArguments = {};
            this.commandOptions = {};
        },
        closeExecutionModal() {
            this.executionResult = null;
        },
        refreshSchedule() {
            this.loadScheduledCommands();
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString();
        },
        formatNextRun(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            const now = new Date();
            const diff = date - now;
            
            if (diff < 0) return 'Overdue';
            if (diff < 60000) return 'In less than a minute';
            if (diff < 3600000) return `In ${Math.floor(diff / 60000)} minutes`;
            if (diff < 86400000) return `In ${Math.floor(diff / 3600000)} hours`;
            return date.toLocaleDateString();
        }
    }
}).mount('#schedule-app');
</script>
@endsection 