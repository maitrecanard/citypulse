import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import MainLayout from './components/layout/MainLayout';
import Login from './components/auth/Login';
import Register from './components/auth/Register';
import Dashboard from './components/dashboard/Dashboard';
import DoleancesList from './components/doleances/DoleancesList';
import DoleanceForm from './components/doleances/DoleanceForm';
import DoleanceDetail from './components/doleances/DoleanceDetail';
import EventsList from './components/events/EventsList';
import EventForm from './components/events/EventForm';
import AnnouncementsList from './components/announcements/AnnouncementsList';
import AnnouncementForm from './components/announcements/AnnouncementForm';
import AlertsList from './components/alerts/AlertsList';
import AlertForm from './components/alerts/AlertForm';
import InterventionsList from './components/interventions/InterventionsList';
import InterventionForm from './components/interventions/InterventionForm';
import VehiclesList from './components/vehicles/VehiclesList';
import VehicleForm from './components/vehicles/VehicleForm';
import Profile from './components/profile/Profile';
import Subscription from './components/subscription/Subscription';
import CityPage from './components/city/CityPage';
import Landing from './components/layout/Landing';
import NotFound from './components/layout/NotFound';

function ProtectedRoute({ children, roles }) {
    const { user, loading } = useAuth();
    if (loading) return <div className="flex items-center justify-center min-h-screen"><div className="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div></div>;
    if (!user) return <Navigate to="/login" replace />;
    if (roles && !roles.includes(user.role)) return <Navigate to="/dashboard" replace />;
    return children;
}

function GuestRoute({ children }) {
    const { user, loading } = useAuth();
    if (loading) return <div className="flex items-center justify-center min-h-screen"><div className="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div></div>;
    if (user) return <Navigate to="/dashboard" replace />;
    return children;
}

function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    <Route path="/" element={<Landing />} />
                    <Route path="/ville/:uuid" element={<CityPage />} />
                    <Route path="/login" element={<GuestRoute><Login /></GuestRoute>} />
                    <Route path="/register" element={<GuestRoute><Register /></GuestRoute>} />

                    <Route element={<ProtectedRoute><MainLayout /></ProtectedRoute>}>
                        <Route path="/dashboard" element={<Dashboard />} />

                        <Route path="/doleances" element={<DoleancesList />} />
                        <Route path="/doleances/new" element={<DoleanceForm />} />
                        <Route path="/doleances/:uuid" element={<DoleanceDetail />} />
                        <Route path="/doleances/:uuid/edit" element={<DoleanceForm />} />

                        <Route path="/events" element={<EventsList />} />
                        <Route path="/events/new" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><EventForm /></ProtectedRoute>} />
                        <Route path="/events/:uuid/edit" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><EventForm /></ProtectedRoute>} />

                        <Route path="/annonces" element={<AnnouncementsList />} />
                        <Route path="/annonces/new" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><AnnouncementForm /></ProtectedRoute>} />
                        <Route path="/annonces/:uuid/edit" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><AnnouncementForm /></ProtectedRoute>} />

                        <Route path="/alertes" element={<AlertsList />} />
                        <Route path="/alertes/new" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><AlertForm /></ProtectedRoute>} />
                        <Route path="/alertes/:uuid/edit" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><AlertForm /></ProtectedRoute>} />

                        <Route path="/interventions" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><InterventionsList /></ProtectedRoute>} />
                        <Route path="/interventions/new" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><InterventionForm /></ProtectedRoute>} />
                        <Route path="/interventions/:uuid/edit" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><InterventionForm /></ProtectedRoute>} />

                        <Route path="/vehicules" element={<ProtectedRoute roles={['maire', 'secretaire', 'agent']}><VehiclesList /></ProtectedRoute>} />
                        <Route path="/vehicules/new" element={<ProtectedRoute roles={['maire', 'secretaire']}><VehicleForm /></ProtectedRoute>} />
                        <Route path="/vehicules/:uuid/edit" element={<ProtectedRoute roles={['maire', 'secretaire']}><VehicleForm /></ProtectedRoute>} />

                        <Route path="/profil" element={<Profile />} />
                        <Route path="/abonnement" element={<ProtectedRoute roles={['maire']}><Subscription /></ProtectedRoute>} />
                    </Route>

                    <Route path="*" element={<NotFound />} />
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}

createRoot(document.getElementById('app')).render(<App />);
