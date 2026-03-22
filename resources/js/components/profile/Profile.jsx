import { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import api from '../../services/api';
import Button from '../ui/Button';
import Input from '../ui/Input';
import Alert from '../ui/Alert';
import Card from '../ui/Card';

export default function Profile() {
    const { user, fetchUser } = useAuth();

    const [profileForm, setProfileForm] = useState({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
    });
    const [profileErrors, setProfileErrors] = useState({});
    const [profileMessage, setProfileMessage] = useState({ type: '', text: '' });
    const [profileLoading, setProfileLoading] = useState(false);

    const [passwordForm, setPasswordForm] = useState({
        current_password: '',
        password: '',
        password_confirmation: '',
    });
    const [passwordErrors, setPasswordErrors] = useState({});
    const [passwordMessage, setPasswordMessage] = useState({ type: '', text: '' });
    const [passwordLoading, setPasswordLoading] = useState(false);

    useEffect(() => {
        if (user) {
            setProfileForm({
                first_name: user.first_name || '',
                last_name: user.last_name || '',
                email: user.email || '',
                phone: user.phone || '',
                address: user.address || '',
            });
        }
    }, [user]);

    const handleProfileChange = (e) => {
        setProfileForm({ ...profileForm, [e.target.name]: e.target.value });
        setProfileErrors({ ...profileErrors, [e.target.name]: '' });
    };

    const handleProfileSubmit = async (e) => {
        e.preventDefault();
        setProfileErrors({});
        setProfileMessage({ type: '', text: '' });
        setProfileLoading(true);
        try {
            await api.put('/profile', profileForm);
            await fetchUser();
            setProfileMessage({ type: 'success', text: 'Profil mis a jour avec succes.' });
        } catch (err) {
            const data = err.response?.data;
            if (data?.errors) setProfileErrors(data.errors);
            else setProfileMessage({ type: 'error', text: data?.message || 'Erreur lors de la mise a jour.' });
        } finally {
            setProfileLoading(false);
        }
    };

    const handlePasswordChange = (e) => {
        setPasswordForm({ ...passwordForm, [e.target.name]: e.target.value });
        setPasswordErrors({ ...passwordErrors, [e.target.name]: '' });
    };

    const handlePasswordSubmit = async (e) => {
        e.preventDefault();
        setPasswordErrors({});
        setPasswordMessage({ type: '', text: '' });
        setPasswordLoading(true);
        try {
            await api.put('/profile/password', passwordForm);
            setPasswordForm({ current_password: '', password: '', password_confirmation: '' });
            setPasswordMessage({ type: 'success', text: 'Mot de passe modifie avec succes.' });
        } catch (err) {
            const data = err.response?.data;
            if (data?.errors) setPasswordErrors(data.errors);
            else setPasswordMessage({ type: 'error', text: data?.message || 'Erreur lors du changement de mot de passe.' });
        } finally {
            setPasswordLoading(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto space-y-8">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Mon profil</h1>
                <p className="text-gray-500 mt-1">Gerez vos informations personnelles</p>
            </div>

            {/* Avatar section */}
            <div className="flex items-center gap-5">
                <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-indigo-500/25">
                    {user?.first_name?.[0]}{user?.last_name?.[0]}
                </div>
                <div>
                    <h2 className="text-lg font-semibold text-gray-900">{user?.first_name} {user?.last_name}</h2>
                    <p className="text-sm text-gray-500 capitalize">{user?.role} &middot; {user?.email}</p>
                </div>
            </div>

            {/* Profile form */}
            <Card title="Informations personnelles">
                {profileMessage.text && (
                    <div className="mb-6">
                        <Alert type={profileMessage.type}>{profileMessage.text}</Alert>
                    </div>
                )}
                <form onSubmit={handleProfileSubmit} className="space-y-5">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <Input
                            label="Prenom"
                            name="first_name"
                            value={profileForm.first_name}
                            onChange={handleProfileChange}
                            error={profileErrors.first_name?.[0]}
                            required
                        />
                        <Input
                            label="Nom"
                            name="last_name"
                            value={profileForm.last_name}
                            onChange={handleProfileChange}
                            error={profileErrors.last_name?.[0]}
                            required
                        />
                    </div>
                    <Input
                        label="Adresse email"
                        type="email"
                        name="email"
                        value={profileForm.email}
                        onChange={handleProfileChange}
                        error={profileErrors.email?.[0]}
                        required
                    />
                    <Input
                        label="Telephone"
                        type="tel"
                        name="phone"
                        value={profileForm.phone}
                        onChange={handleProfileChange}
                        placeholder="06 12 34 56 78"
                        error={profileErrors.phone?.[0]}
                    />
                    <Input
                        label="Adresse"
                        name="address"
                        value={profileForm.address}
                        onChange={handleProfileChange}
                        placeholder="12 rue de la Mairie, 75001 Paris"
                        error={profileErrors.address?.[0]}
                    />
                    <Button type="submit" loading={profileLoading}>
                        Sauvegarder
                    </Button>
                </form>
            </Card>

            {/* Password form */}
            <Card title="Changer le mot de passe">
                {passwordMessage.text && (
                    <div className="mb-6">
                        <Alert type={passwordMessage.type}>{passwordMessage.text}</Alert>
                    </div>
                )}
                <form onSubmit={handlePasswordSubmit} className="space-y-5">
                    <Input
                        label="Mot de passe actuel"
                        type="password"
                        name="current_password"
                        value={passwordForm.current_password}
                        onChange={handlePasswordChange}
                        error={passwordErrors.current_password?.[0]}
                        required
                    />
                    <Input
                        label="Nouveau mot de passe"
                        type="password"
                        name="password"
                        value={passwordForm.password}
                        onChange={handlePasswordChange}
                        placeholder="Minimum 8 caracteres"
                        error={passwordErrors.password?.[0]}
                        required
                    />
                    <Input
                        label="Confirmer le nouveau mot de passe"
                        type="password"
                        name="password_confirmation"
                        value={passwordForm.password_confirmation}
                        onChange={handlePasswordChange}
                        error={passwordErrors.password_confirmation?.[0]}
                        required
                    />
                    <Button type="submit" loading={passwordLoading}>
                        Changer le mot de passe
                    </Button>
                </form>
            </Card>
        </div>
    );
}
