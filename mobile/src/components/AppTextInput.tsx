import React from 'react';
import { View, Text, TextInput, TextInputProps } from 'react-native';

interface AppTextInputProps extends TextInputProps {
    label: string;
    icon?: React.ReactNode;
    rightIcon?: React.ReactNode;
}

export default function AppTextInput({
    label,
    icon,
    rightIcon,
    ...props
}: AppTextInputProps) {
    return (
        <View className="w-full mb-4">
            <Text className="text-zinc-800 text-sm font-semibold mb-2 uppercase tracking-wide">
                {label}
            </Text>
            <View className="h-14 w-full bg-zinc-50 border border-zinc-200 rounded-2xl flex-row items-center px-4">
                {icon && <View className="mr-3">{icon}</View>}
                <TextInput
                    className="flex-1 text-black text-base h-full"
                    placeholderTextColor="#A1A1AA"
                    {...props}
                />
                {rightIcon && <View className="ml-3">{rightIcon}</View>}
            </View>
        </View>
    );
}
