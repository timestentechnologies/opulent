<!-- Place Order Modal -->
<div id="placeOrderModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full relative">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Place Your Order</h3>
                    <button onclick="closePlaceOrderModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <form id="placeOrderForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Type</label>
                            <select name="service_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Select a service</option>
                                <?php
                                $sql_services = "SELECT * FROM service";
                                $result_services = $conn->query($sql_services);
                                while ($service = $result_services->fetch_assoc()): ?>
                                    <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['prize']; ?>">
                                        <?php echo htmlspecialchars($service['sname']); ?> - Ksh.<?php echo number_format($service['prize'], 2); ?>/kg
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                            <input type="number" name="weight" step="0.1" min="0.1" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Date</label>
                            <input type="date" name="pickup_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                            <input type="date" name="delivery_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Price:</span>
                            <span id="priceDisplay" class="text-xl font-bold text-primary">Ksh.0.00</span>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closePlaceOrderModal()" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 z-[101] hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Order Placed Successfully!</h3>
                    <button onclick="closeSuccessModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-check-line text-green-500 text-3xl"></i>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tracking Number:</span>
                        <span id="successTrackingNumber" class="font-medium text-primary"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Service:</span>
                        <span id="successService" class="font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Weight:</span>
                        <span id="successWeight" class="font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Price:</span>
                        <span id="successPrice" class="font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pickup Date:</span>
                        <span id="successPickupDate" class="font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Delivery Date:</span>
                        <span id="successDeliveryDate" class="font-medium"></span>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button onclick="closeSuccessModal()" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">Close</button>
                </div>
            </div>
        </div>
    </div>
</div> 