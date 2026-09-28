import React, { useContext } from 'react';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useTranslation } from 'react-i18next';
import { AuthContext } from '../context/AuthContext';

import HomeScreen from '../screens/HomeScreen';
import LoginScreen from '../screens/LoginScreen';
import PosScreen from '../screens/PosScreen';
import DashboardScreen from '../screens/DashboardScreen';
import TransactionsScreen from '../screens/TransactionsScreen';
import ProductsScreen from '../screens/ProductsScreen';
import SettingsScreen from '../screens/SettingsScreen';

import {
  Home,
  ShoppingBag,
  Receipt,
  Settings,
} from 'lucide-react-native';

const Tab = createBottomTabNavigator();
const Stack = createNativeStackNavigator();

function MainTabNavigator() {
  const { t } = useTranslation();

  return (
    <Tab.Navigator
      initialRouteName="HomeTab"
      screenOptions={{
        headerStyle: {
          backgroundColor: '#6B2E3E',
          shadowColor: 'transparent',
          elevation: 0,
          borderBottomWidth: 1,
          borderBottomColor: '#C9A96E',
        },
        headerTitleStyle: {
          fontWeight: '700',
          fontSize: 17,
          color: '#F9F6F0',
          letterSpacing: 0.5,
        },
        tabBarStyle: {
          backgroundColor: '#1E1E1E',
          borderTopWidth: 1,
          borderTopColor: '#C9A96E',
          height: 60,
          paddingBottom: 8,
          paddingTop: 8,
        },
        tabBarActiveTintColor: '#C9A96E',
        tabBarInactiveTintColor: '#94A3B8',
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '600',
        },
      }}
    >
      <Tab.Screen
        name="HomeTab"
        component={HomeScreen}
        options={{
          headerShown: false,
          title: t('nav.home'),
          tabBarIcon: ({ color, size }) => <Home size={size} color={color} />,
        }}
      />
      <Tab.Screen
        name="PosTab"
        component={PosScreen}
        options={{
          title: t('nav.pos'),
          headerTitle: t('pos.title'),
          tabBarIcon: ({ color, size }) => <ShoppingBag size={size} color={color} />,
        }}
      />
      <Tab.Screen
        name="TransactionsTab"
        component={TransactionsScreen}
        options={{
          title: t('nav.transactions'),
          headerTitle: t('transactions.title'),
          tabBarIcon: ({ color, size }) => <Receipt size={size} color={color} />,
        }}
      />
      <Tab.Screen
        name="SettingsTab"
        component={SettingsScreen}
        options={{
          title: t('nav.settings'),
          headerTitle: t('settings.title'),
          tabBarIcon: ({ color, size }) => <Settings size={size} color={color} />,
        }}
      />
    </Tab.Navigator>
  );
}

export default function AppNavigator() {
  const { user, isLoading } = useContext(AuthContext);

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#2563EB" />
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        {!user ? (
          <>
            <Stack.Screen name="Home" component={HomeScreen} />
            <Stack.Screen name="Login" component={LoginScreen} />
          </>
        ) : (
          <>
            <Stack.Screen name="Main" component={MainTabNavigator} />
            <Stack.Screen
              name="Dashboard"
              component={DashboardScreen}
              options={{
                headerShown: true,
                title: 'Dashboard Penjualan',
              }}
            />
            <Stack.Screen
              name="Products"
              component={ProductsScreen}
              options={{
                headerShown: true,
                title: 'Katalog & Stok Produk',
              }}
            />
          </>
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  loadingContainer: {
    flex: 1,
    backgroundColor: '#F8FAFC',
    alignItems: 'center',
    justifyContent: 'center',
  },
});
