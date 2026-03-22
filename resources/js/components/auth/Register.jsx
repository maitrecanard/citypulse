import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import Button from '../ui/Button';
import Input from '../ui/Input';
import Alert from '../ui/Alert';

export default function Register() {
    const { register } = useAuth();
    const navigate = useNavigate();
    const [form, setForm] = useState({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const [errors, setErrors] = useState({});
    const [globalError, setGlobalError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
        setErrors({ ...errors, [e.target.name]: '' });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setGlobalError('');
        setErrors({});
        setLoading(true);
        try {
            await register(form);
            navigate('/dashboard');
        } catch (err) {
            const data = err.response?.data;
            if (data?.errors) {
                setErrors(data.errors);
            } else {
                setGlobalError(data?.message || "Une erreur est survenue lors de l'inscription.");
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-12">
            <div className="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-purple-400/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
            <div className="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-purple-400/10 to-pink-400/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2" />

            <div className="relative w-full max-w-md">
                <div className="text-center mb-8">
                    <Link to="/" className="inline-flex items-center gap-2">
                        <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
                            <svg className="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                            </svg>
                        </div>
                        <span className="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            CityPulse
                        </span>
                    </Link>
                </div>

                <div className="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8">
                    <h2 className="text-2xl font-bold text-gray-900 mb-1">Creer un compte</h2>
                    <p className="text-gray-500 mb-6">Rejoignez votre commune sur CityPulse</p>

                    {globalError && (
                        <div className="mb-4">
                            <Alert type="error">{globalError}</Alert>
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <Input
                                label="Prenom"
                                name="first_name"
                                value={form.first_name}
                                onChange={handleChange}
                                placeholder="Jean"
                                error={errors.first_name?.[0]}
                                required
                            />
                            <Input
                                label="Nom"
                                name="last_name"
                                value={form.last_name}
                                onChange={handleChange}
                                placeholder="Dupont"
                                error={errors.last_name?.[0]}
                                required
                            />
                        </div>
                        <Input
                            label="Adresse email"
                            type="email"
                            name="email"
                            value={form.email}
                            onChange={handleChange}
                            placeholder="vous@commune.fr"
                            error={errors.email?.[0]}
                            required
                        />
                        <Input
                            label="Mot de passe"
                            type="password"
                            name="password"
                            value={form.password}
                            onChange={handleChange}
                            placeholder="Minimum 8 caracteres"
                            error={errors.password?.[0]}
                            required
                        />
                        <Input
                            label="Confirmer le mot de passe"
                            type="password"
                            name="password_confirmation"
                            value={form.password_confirmation}
                            onChange={handleChange}
                            placeholder="Confirmez votre mot de passe"
                            error={errors.password_confirmation?.[0]}
                            required
                        />
                        <Button type="submit" loading={loading} className="w-full mt-2">
                            Creer mon compte
                        </Button>
                    </form>
                </div>

                <p className="text-center mt-6 text-sm text-gray-600">
                    Deja un compte ?{' '}
                    <Link to="/login" className="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                        Se connecter
                    </Link>
                </p>
            </div>
        </div>
    );
}
