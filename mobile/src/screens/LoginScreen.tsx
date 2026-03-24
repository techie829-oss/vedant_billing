import React, { useState } from 'react';
import {
    View,
    Text,
    KeyboardAvoidingView,
    Platform,
    Alert,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Zap, Mail, Lock, EyeOff, Eye } from 'lucide-react-native';
import { useAuthStore } from '../store/useAuthStore';
import AppTextInput from '../components/AppTextInput';
import AppButton from '../components/AppButton';

export default function LoginScreen() {
    const { login, isLoading, error } = useAuthStore();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);

    const handleLogin = async () => {
        if (!email.trim() || !password.trim()) {
            Alert.alert('Validation', 'Please enter your email and password.');
            return;
        }
        try {
            await login(email.trim(), password);
        } catch (err: any) {
            Alert.alert('Login Failed', err.message || 'Something went wrong.');
        }
    };

    return (
        <SafeAreaView className="flex-1 bg-white">
            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                className="flex-1 px-6 justify-center"
            >
                <View className="items-center mb-12">
                    <Zap size={48} color="#000" strokeWidth={2} />
                    <Text className="text-4xl font-black text-black mt-2 tracking-tighter">
                        Vedant
                    </Text>
                </View>

                <View className="mb-8">
                    <Text className="text-3xl font-extrabold text-black mb-2 tracking-tight">
                        Sign In
                    </Text>
                    <Text className="text-base text-zinc-500">
                        Manage your billing on the go
                    </Text>
                </View>

                {error ? (
                    <View className="bg-red-50 border border-red-200 rounded-2xl px-4 py-3 mb-4">
                        <Text className="text-red-600 text-sm">{error}</Text>
                    </View>
                ) : null}

                <AppTextInput
                    label="Email"
                    placeholder="name@company.com"
                    value={email}
                    onChangeText={setEmail}
                    keyboardType="email-address"
                    autoCapitalize="none"
                    icon={<Mail size={20} color="#A1A1AA" />}
                />

                <AppTextInput
                    label="Password"
                    placeholder="••••••••"
                    value={password}
                    onChangeText={setPassword}
                    secureTextEntry={!showPassword}
                    icon={<Lock size={20} color="#A1A1AA" />}
                    rightIcon={
                        <View onTouchEnd={() => setShowPassword((v) => !v)}>
                            {showPassword
                                ? <Eye size={20} color="#A1A1AA" />
                                : <EyeOff size={20} color="#A1A1AA" />}
                        </View>
                    }
                />

                <View className="mt-4">
                    <AppButton
                        title="Continue"
                        onPress={handleLogin}
                        isLoading={isLoading}
                    />
                </View>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}
