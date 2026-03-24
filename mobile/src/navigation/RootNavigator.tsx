import React, { useEffect, useState } from 'react';
import { View, ActivityIndicator } from 'react-native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Home, FileText, Users, Box } from 'lucide-react-native';

import { useAuthStore } from '../store/useAuthStore';
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import InvoicesScreen from '../screens/InvoicesScreen';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

const DummyScreen = () => <View style={{ flex: 1, backgroundColor: '#F4F4F5' }} />;

// Tab navigator is a screen component — NOT a nested navigator wrapper
function MainTabsScreen() {
    return (
        <Tab.Navigator
            screenOptions={{
                headerShown: false,
                tabBarStyle: {
                    position: 'absolute',
                    bottom: 30,
                    left: 20,
                    right: 20,
                    elevation: 0,
                    backgroundColor: '#FFFFFF',
                    borderRadius: 32,
                    height: 64,
                    shadowColor: '#000',
                    shadowOffset: { width: 0, height: 10 },
                    shadowOpacity: 0.1,
                    shadowRadius: 20,
                    paddingBottom: 0,
                    paddingTop: 8,
                    borderTopWidth: 0,
                },
                tabBarActiveTintColor: '#000000',
                tabBarInactiveTintColor: '#A1A1AA',
            }}
        >
            <Tab.Screen
                name="Home"
                component={DashboardScreen}
                options={{ tabBarIcon: ({ color }) => <Home color={color} size={22} /> }}
            />
            <Tab.Screen
                name="Invoices"
                component={InvoicesScreen}
                options={{ tabBarIcon: ({ color }) => <FileText color={color} size={22} /> }}
            />
            <Tab.Screen
                name="Customers"
                component={DummyScreen}
                options={{ tabBarIcon: ({ color }) => <Users color={color} size={22} /> }}
            />
            <Tab.Screen
                name="Items"
                component={DummyScreen}
                options={{ tabBarIcon: ({ color }) => <Box color={color} size={22} /> }}
            />
        </Tab.Navigator>
    );
}

export default function RootNavigator() {
    const { isAuthenticated, restoreSession } = useAuthStore();
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        restoreSession().finally(() => setIsLoading(false));
    }, []);

    if (isLoading) {
        return (
            <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff' }}>
                <ActivityIndicator size="large" color="#000" />
            </View>
        );
    }

    // Official React Navigation auth pattern:
    // ONE Stack.Navigator with conditional screens — no nested navigator wrappers
    return (
        <Stack.Navigator screenOptions={{ headerShown: false, animation: 'fade' }}>
            {isAuthenticated ? (
                <Stack.Screen name="MainTabs" component={MainTabsScreen} />
            ) : (
                <Stack.Screen name="Login" component={LoginScreen} />
            )}
        </Stack.Navigator>
    );
}
