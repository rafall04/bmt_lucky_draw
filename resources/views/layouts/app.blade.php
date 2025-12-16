<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full w-full overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'BMT Lucky Draw') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <script>
            // Global storage for intervals to persist across Livewire re-renders
            window.undianIntervals = window.undianIntervals || {};
            window.undianEventHandlers = window.undianEventHandlers || {};
            
            function undianData() {
                return {
                    rolling: false,
                    displayNo: '---',
                    displayName: '---',
                    displayAlamat: '---',
                    displayCabang: '---',
                    intervalId: null,
                    dummyData: [],
                    currentIndex: 0,
                    componentId: '',
                    isStopped: false,
                    waitingForResult: false,
                    stopping: false,
                    pendingWinner: null,
                    stopTimeout: null,
                    animationSpeed: 50,
                    
                    init() {
                        this.componentId = this.$el.getAttribute('wire:id');
                        
                        // CRITICAL: Cleanup any existing interval for this component
                        if (window.undianIntervals[this.componentId]) {
                            clearInterval(window.undianIntervals[this.componentId]);
                            delete window.undianIntervals[this.componentId];
                        }
                        
                        // CRITICAL: Cleanup any existing event handler
                        if (window.undianEventHandlers[this.componentId]) {
                            window.removeEventListener('winner-selected', window.undianEventHandlers[this.componentId]);
                            delete window.undianEventHandlers[this.componentId];
                        }
                        
                        try {
                            const dataAttr = this.$el.getAttribute('data-dummy');
                            if (dataAttr) {
                                const decoded = atob(dataAttr);
                                this.dummyData = JSON.parse(decoded);
                                if (this.dummyData && this.dummyData.length > 0) {
                                    this.shuffleArray(this.dummyData);
                                }
                            }
                        } catch (e) {
                            console.error('Error loading dummy data:', e);
                            this.dummyData = [];
                        }
                        
                        // Create event handler and store it globally
                        const self = this;
                        const winnerHandler = (event) => {
                            if (event && event.detail && event.detail.pemenang) {
                                self.updateDisplayFromPemenang(event.detail.pemenang);
                            }
                        };
                        window.undianEventHandlers[this.componentId] = winnerHandler;
                        window.addEventListener('winner-selected', winnerHandler);
                        
                        // Cleanup on destroy
                        this.$el.addEventListener('livewire:destroy', () => {
                            this.stopRolling();
                            if (window.undianEventHandlers[this.componentId]) {
                                window.removeEventListener('winner-selected', window.undianEventHandlers[this.componentId]);
                                delete window.undianEventHandlers[this.componentId];
                            }
                        });
                    },
                    
                    shuffleArray(array) {
                        for (let i = array.length - 1; i > 0; i--) {
                            const j = Math.floor(Math.random() * (i + 1));
                            [array[i], array[j]] = [array[j], array[i]];
                        }
                    },
                    
                    startRolling() {
                        // CRITICAL: Clear winner data ONLY when START is clicked
                        // This is the ONLY place where data should be cleared
                        this.displayNo = '---';
                        this.displayName = '---';
                        this.displayAlamat = '---';
                        this.displayCabang = '---';
                        
                        // Stop any existing animation
                        this.stopRolling();
                        
                        // Reset flags for new round
                        this.isStopped = false;
                        this.waitingForResult = false;
                        this.stopping = false;
                        this.pendingWinner = null;
                        this.animationSpeed = 50;
                        this.rolling = true;
                        
                        if (this.dummyData && this.dummyData.length > 0) {
                            this.shuffleArray(this.dummyData);
                        }
                        
                        this.currentIndex = 0;
                        const self = this;
                        const intervalId = setInterval(function() {
                            // Hard braking logic: If stopping, check for winner data
                            if (self.stopping) {
                                // Check if winner data has arrived
                                if (self.pendingWinner) {
                                    // Winner data ready - update display and stop immediately
                                    self.displayNo = self.pendingWinner.no_rekening || '---';
                                    self.displayName = self.pendingWinner.nama || '---';
                                    self.displayAlamat = self.pendingWinner.alamat || '---';
                                    self.displayCabang = self.pendingWinner.cabang || '---';
                                    
                                    // Stop everything
                                    clearInterval(intervalId);
                                    if (window.undianIntervals[self.componentId] === intervalId) {
                                        delete window.undianIntervals[self.componentId];
                                    }
                                    self.intervalId = null;
                                    self.isStopped = true;
                                    self.rolling = false;
                                    self.waitingForResult = false;
                                    self.stopping = false;
                                    self.pendingWinner = null;
                                    
                                    // Clear timeout
                                    if (self.stopTimeout) {
                                        clearTimeout(self.stopTimeout);
                                        self.stopTimeout = null;
                                    }
                                    return;
                                }
                                // Still waiting - slow down animation (aggressive deceleration)
                                // Increase interval time gradually: 50ms -> 75ms -> 112ms -> 168ms -> 200ms
                                self.animationSpeed = Math.min(self.animationSpeed * 1.5, 200);
                            }
                            
                            if (self.isStopped || (!self.rolling && !self.waitingForResult && !self.stopping)) {
                                clearInterval(intervalId);
                                if (window.undianIntervals[self.componentId] === intervalId) {
                                    delete window.undianIntervals[self.componentId];
                                }
                                self.intervalId = null;
                                return;
                            }
                            
                            if (self.dummyData && self.dummyData.length > 0) {
                                const data = self.dummyData[self.currentIndex % self.dummyData.length];
                                self.displayNo = data.no_rekening || '---';
                                self.displayName = data.nama || '---';
                                self.displayAlamat = data.alamat || '---';
                                self.displayCabang = data.cabang || '---';
                                self.currentIndex++;
                            }
                            
                            // Dynamic interval speed adjustment
                            if (self.stopping && self.animationSpeed > 50) {
                                clearInterval(intervalId);
                                if (window.undianIntervals[self.componentId] === intervalId) {
                                    delete window.undianIntervals[self.componentId];
                                }
                                self.intervalId = setInterval(arguments.callee, self.animationSpeed);
                                window.undianIntervals[self.componentId] = self.intervalId;
                            }
                        }, this.animationSpeed);
                        
                        this.intervalId = intervalId;
                        window.undianIntervals[this.componentId] = intervalId;
                    },
                    
                    stopRolling() {
                        this.isStopped = true;
                        this.rolling = false;
                        this.waitingForResult = false;
                        this.stopping = false;
                        this.pendingWinner = null;
                        this.animationSpeed = 50;
                        
                        // Clear timeout if exists
                        if (this.stopTimeout) {
                            clearTimeout(this.stopTimeout);
                            this.stopTimeout = null;
                        }
                        
                        // Clear local interval/timeout
                        if (this.intervalId) {
                            clearTimeout(this.intervalId);
                            this.intervalId = null;
                        }
                        
                        // CRITICAL: Also clear from global storage
                        if (this.componentId && window.undianIntervals[this.componentId]) {
                            clearTimeout(window.undianIntervals[this.componentId]);
                            delete window.undianIntervals[this.componentId];
                        }
                    },
                    
                    handleStopClick() {
                        if (this.isStopped || !this.rolling || this.waitingForResult || this.stopping) {
                            return;
                        }
                        
                        // Start aggressive braking
                        this.stopping = true;
                        this.waitingForResult = true;
                        this.animationSpeed = 50; // Reset speed for deceleration
                        this.pendingWinner = null; // Clear any previous pending winner
                        
                        // Safety net: If no response in 2 seconds, show error
                        const self = this;
                        this.stopTimeout = setTimeout(function() {
                            if (self.stopping && !self.pendingWinner) {
                                alert('Koneksi timeout. Silakan coba lagi.');
                                self.stopRolling();
                                self.rolling = true; // Allow retry
                                self.waitingForResult = false;
                                self.stopping = false;
                            }
                        }, 2000);
                        
                        // Panggil Livewire untuk pick winner
                        const wireElement = this.$el.closest('[wire\\:id]');
                        if (wireElement && window.Livewire) {
                            const wireId = wireElement.getAttribute('wire:id');
                            const component = window.Livewire.find(wireId);
                            if (component && component.call) {
                                component.call('pickWinner');
                            }
                        }
                    },
                    
                    updateDisplayFromPemenang(pemenang) {
                        if (pemenang && pemenang.no_rekening) {
                            // If we're in stopping mode, store winner data for smooth transition
                            if (this.stopping) {
                                this.pendingWinner = pemenang;
                                // Interval will pick this up and update display
                                return;
                            }
                            
                            // CRITICAL: Update display with winner data - this data will PERSIST
                            // DO NOT clear this data - it must remain until START is clicked
                            this.displayNo = pemenang.no_rekening || '---';
                            this.displayName = pemenang.nama || '---';
                            this.displayAlamat = pemenang.alamat || '---';
                            this.displayCabang = pemenang.cabang || '---';
                            
                            // Stop animation only - DO NOT clear display data
                            this.isStopped = true;
                            this.rolling = false;
                            this.waitingForResult = false;
                            this.stopping = false;
                            this.pendingWinner = null;
                            
                            // Clear timeout
                            if (this.stopTimeout) {
                                clearTimeout(this.stopTimeout);
                                this.stopTimeout = null;
                            }
                            
                            // Clear interval setelah display di-update
                            if (this.intervalId) {
                                clearTimeout(this.intervalId);
                                this.intervalId = null;
                            }
                            
                            // Clear from global storage
                            if (this.componentId && window.undianIntervals[this.componentId]) {
                                clearTimeout(window.undianIntervals[this.componentId]);
                                delete window.undianIntervals[this.componentId];
                            }
                        }
                    },
                    
                    handleWinnerSelected(event) {
                        if (event.detail && event.detail.pemenang) {
                            const winnerData = event.detail.pemenang;
                            
                            // CRITICAL: Force immediate update with server data BEFORE stopping animation
                            // This ensures UI shows the REAL winner from database, not dummy data
                            this.displayNo = winnerData.no_rekening || '---';
                            this.displayName = winnerData.nama || '---';
                            this.displayAlamat = winnerData.alamat || '---';
                            this.displayCabang = winnerData.cabang || '---';
                            
                            // Clear pending winner to prevent race condition
                            this.pendingWinner = null;
                            
                            // Stop animation immediately after display update
                            if (this.intervalId) {
                                clearTimeout(this.intervalId);
                                this.intervalId = null;
                            }
                            
                            // Clear from global storage
                            if (this.componentId && window.undianIntervals[this.componentId]) {
                                clearTimeout(window.undianIntervals[this.componentId]);
                                delete window.undianIntervals[this.componentId];
                            }
                            
                            // Reset animation flags
                            this.rolling = false;
                            this.stopping = false;
                            this.waitingForResult = false;
                            this.isStopped = true;
                            this.animationSpeed = 50;
                            
                            // Clear timeout if exists
                            if (this.stopTimeout) {
                                clearTimeout(this.stopTimeout);
                                this.stopTimeout = null;
                            }
                            
                            // Data will persist on screen until START is clicked again
                        }
                    }
                };
            }
        </script>
</head>
    <body class="font-sans antialiased m-0 p-0 overflow-hidden h-screen w-screen">
        {{ $slot }}
        @livewireScripts
    </body>
</html>

