import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    FlatList,
    TouchableOpacity,
    TextInput,
    ActivityIndicator,
    RefreshControl,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Plus, Search } from 'lucide-react-native';
import apiClient from '../api/client';

interface Invoice {
    id: number;
    invoice_number: string;
    party?: { name: string };
    customer_name?: string;
    total_amount: number;
    created_at: string;
    status: string;
}

export default function InvoicesScreen() {
    const [invoices, setInvoices] = useState<Invoice[]>([]);
    const [filtered, setFiltered] = useState<Invoice[]>([]);
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchInvoices = async (silent = false) => {
        if (!silent) setLoading(true);
        try {
            const res = await apiClient.get('/invoices');
            const list: Invoice[] = res.data?.data ?? res.data ?? [];
            setInvoices(list);
            setFiltered(list);
        } catch (_) {
            // keep state on error
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        fetchInvoices();
    }, []);

    useEffect(() => {
        if (!query.trim()) {
            setFiltered(invoices);
        } else {
            const q = query.toLowerCase();
            setFiltered(
                invoices.filter(
                    (inv) =>
                        (inv.party?.name ?? inv.customer_name ?? '').toLowerCase().includes(q) ||
                        inv.invoice_number?.toLowerCase().includes(q)
                )
            );
        }
    }, [query, invoices]);

    const formatAmount = (val: number) => {
        if (!val) return '₹0';
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0,
        }).format(val);
    };

    const formatDate = (dateStr: string) => {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
    };

    const renderItem = ({ item }: { item: Invoice }) => {
        const status = (item.status ?? 'draft').toUpperCase();
        const name = item.party?.name ?? item.customer_name ?? 'Unknown';

        let statusBg = 'bg-zinc-100';
        let statusText = 'text-zinc-500';
        if (status === 'PAID' || status === 'FINALIZED') {
            statusBg = 'bg-zinc-800';
            statusText = 'text-white';
        } else if (status === 'PENDING' || status === 'SENT') {
            statusBg = 'bg-white border border-zinc-200';
            statusText = 'text-zinc-500';
        }

        return (
            <TouchableOpacity className="flex-row items-center justify-between bg-white p-4 rounded-3xl mb-3 border border-zinc-50 shadow-sm">
                <View className="flex-1 mr-3">
                    <Text className="text-base font-bold text-black" numberOfLines={1}>{name}</Text>
                    <Text className="text-xs text-zinc-400 mt-1">
                        {item.invoice_number} • {formatDate(item.created_at)}
                    </Text>
                </View>
                <View className="items-end">
                    <Text className="text-base font-black text-black mb-1">
                        {formatAmount(item.total_amount)}
                    </Text>
                    <View className={`px-2 py-1 rounded-md ${statusBg}`}>
                        <Text className={`text-[10px] font-bold tracking-widest ${statusText}`}>
                            {status}
                        </Text>
                    </View>
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <SafeAreaView className="flex-1 bg-zinc-50">
            {/* Header */}
            <View className="flex-row justify-between items-center px-6 pt-6 pb-4 bg-white">
                <Text className="text-3xl font-black text-black tracking-tight">Invoices</Text>
                <TouchableOpacity className="flex-row items-center bg-black px-4 py-2.5 rounded-2xl">
                    <Plus size={16} color="#FFF" />
                    <Text className="text-white text-sm font-bold ml-1">New</Text>
                </TouchableOpacity>
            </View>

            {/* Search */}
            <View className="px-6 py-4">
                <View className="flex-row items-center bg-white h-12 rounded-2xl px-4 border border-zinc-100 shadow-sm">
                    <Search size={18} color="#A1A1AA" />
                    <TextInput
                        className="flex-1 ml-2 text-black text-sm"
                        placeholder="Search invoices..."
                        placeholderTextColor="#A1A1AA"
                        value={query}
                        onChangeText={setQuery}
                    />
                </View>
            </View>

            {loading ? (
                <View className="flex-1 items-center justify-center">
                    <ActivityIndicator size="large" color="#000" />
                </View>
            ) : (
                <FlatList
                    data={filtered}
                    keyExtractor={(item) => String(item.id)}
                    renderItem={renderItem}
                    contentContainerStyle={{ paddingHorizontal: 24, paddingBottom: 120 }}
                    showsVerticalScrollIndicator={false}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={() => {
                                setRefreshing(true);
                                fetchInvoices(true);
                            }}
                        />
                    }
                    ListEmptyComponent={
                        <View className="items-center mt-16">
                            <Text className="text-zinc-400 text-base">No invoices found</Text>
                        </View>
                    }
                />
            )}
        </SafeAreaView>
    );
}
