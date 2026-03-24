import React, { useEffect, useState } from 'react';
import {
    View,
    Text,
    ScrollView,
    TouchableOpacity,
    ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Bell, FilePlus, Receipt, Scan, LogOut } from 'lucide-react-native';
import apiClient from '../api/client';
import { useAuthStore } from '../store/useAuthStore';

interface DashboardData {
    sales_revenue?: number | string;
    outstanding?: number | string;
}

export default function DashboardScreen() {
    const { user, logout } = useAuthStore();
    const [data, setData] = useState<DashboardData>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchDashboard = async () => {
            try {
                const res = await apiClient.get('/dashboard');
                setData(res.data?.data ?? res.data ?? {});
            } catch (_) {
                // keep defaults on error
            } finally {
                setLoading(false);
            }
        };
        fetchDashboard();
    }, []);

    const formatAmount = (val?: number | string) => {
        if (val === null || val === undefined) return '—';
        const num = typeof val === 'string' ? parseFloat(val) : val;
        if (isNaN(num)) return '—';
        if (num >= 100000) return `₹${(num / 100000).toFixed(1)}L`;
        if (num >= 1000) return `₹${(num / 1000).toFixed(1)}k`;
        return `₹${num.toFixed(0)}`;
    };

    return (
        <SafeAreaView className="flex-1 bg-zinc-50">
            <ScrollView
                className="flex-1 px-6 pt-6"
                contentContainerStyle={{ paddingBottom: 120 }}
            >
                {/* Header */}
                <View className="flex-row justify-between items-center mb-8">
                    <View>
                        <Text className="text-sm text-zinc-500 mb-1">Good day,</Text>
                        <Text className="text-2xl font-black text-black tracking-tight">
                            {user?.name ?? 'Vedant Books'}
                        </Text>
                    </View>
                    <View className="flex-row gap-3">
                        <TouchableOpacity className="w-12 h-12 bg-zinc-100 rounded-full items-center justify-center">
                            <Bell size={20} color="#000" />
                        </TouchableOpacity>
                        <TouchableOpacity
                            className="w-12 h-12 bg-zinc-100 rounded-full items-center justify-center"
                            onPress={() => logout()}
                        >
                            <LogOut size={20} color="#000" />
                        </TouchableOpacity>
                    </View>
                </View>

                {/* Metrics */}
                {loading ? (
                    <View className="flex-row justify-between mb-8">
                        <View className="flex-1 bg-black p-5 rounded-3xl mr-2 items-center justify-center h-28">
                            <ActivityIndicator color="#fff" />
                        </View>
                        <View className="flex-1 bg-white p-5 rounded-3xl ml-2 border border-zinc-100 items-center justify-center h-28">
                            <ActivityIndicator color="#000" />
                        </View>
                    </View>
                ) : (
                    <View className="flex-row justify-between mb-8">
                        <View className="flex-1 bg-black p-5 rounded-3xl mr-2">
                            <Text className="text-sm text-zinc-400 mb-2">Sales Revenue</Text>
                            <Text className="text-3xl font-black text-white tracking-tighter">
                                {formatAmount(data.sales_revenue)}
                            </Text>
                        </View>
                        <View className="flex-1 bg-white p-5 rounded-3xl ml-2 border border-zinc-100 shadow-sm">
                            <Text className="text-sm text-zinc-500 mb-2">Outstanding</Text>
                            <Text className="text-3xl font-black text-black tracking-tighter">
                                {formatAmount(data.outstanding)}
                            </Text>
                        </View>
                    </View>
                )}

                {/* Quick Actions */}
                <Text className="text-xl font-bold text-black mb-4">Quick Actions</Text>
                <View className="flex-row justify-between">
                    <TouchableOpacity className="flex-1 bg-white items-center justify-center p-4 rounded-3xl border border-zinc-100 mr-2 h-28 shadow-sm">
                        <FilePlus size={24} color="#000" />
                        <Text className="text-sm font-medium text-black mt-2">Invoice</Text>
                    </TouchableOpacity>
                    <TouchableOpacity className="flex-1 bg-white items-center justify-center p-4 rounded-3xl border border-zinc-100 mx-1 h-28 shadow-sm">
                        <Receipt size={24} color="#000" />
                        <Text className="text-sm font-medium text-black mt-2">Receipt</Text>
                    </TouchableOpacity>
                    <TouchableOpacity className="flex-1 bg-white items-center justify-center p-4 rounded-3xl border border-zinc-100 ml-2 h-28 shadow-sm">
                        <Scan size={24} color="#000" />
                        <Text className="text-sm font-medium text-black mt-2">Scan</Text>
                    </TouchableOpacity>
                </View>
            </ScrollView>
        </SafeAreaView>
    );
}
