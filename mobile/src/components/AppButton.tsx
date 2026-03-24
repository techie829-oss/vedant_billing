import React from 'react';
import { View, Text, TouchableOpacity, ActivityIndicator } from 'react-native';

interface AppButtonProps {
    title: string;
    onPress: () => void;
    isLoading?: boolean;
    className?: string;
    variant?: 'primary' | 'outline' | 'ghost';
}

export default function AppButton({
    title,
    onPress,
    isLoading = false,
    className = '',
    variant = 'primary'
}: AppButtonProps) {
    const getContainerStyles = () => {
        switch (variant) {
            case 'outline': return 'border-2 border-zinc-200 bg-transparent';
            case 'ghost': return 'bg-transparent';
            case 'primary':
            default: return 'bg-black';
        }
    };

    const getTitleStyles = () => {
        switch (variant) {
            case 'outline':
            case 'ghost': return 'text-black';
            case 'primary':
            default: return 'text-white';
        }
    };

    return (
        <TouchableOpacity
            activeOpacity={0.8}
            onPress={onPress}
            disabled={isLoading}
            className={`h-14 w-full rounded-2xl flex-row items-center justify-center ${getContainerStyles()} ${className}`}
        >
            {isLoading ? (
                <ActivityIndicator color={variant === 'primary' ? '#fff' : '#000'} />
            ) : (
                <Text className={`text-base font-bold ${getTitleStyles()}`}>
                    {title}
                </Text>
            )}
        </TouchableOpacity>
    );
}
