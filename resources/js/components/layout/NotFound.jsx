import { Link } from 'react-router-dom';

export default function NotFound() {
    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4">
            <div className="text-center">
                <p className="text-8xl font-extrabold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    404
                </p>
                <h1 className="mt-4 text-2xl font-bold text-gray-900">Page introuvable</h1>
                <p className="mt-2 text-gray-600">
                    Desolee, la page que vous recherchez n'existe pas ou a ete deplacee.
                </p>
                <Link
                    to="/"
                    className="inline-flex items-center gap-2 mt-8 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25"
                >
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Retour a l'accueil
                </Link>
            </div>
        </div>
    );
}
