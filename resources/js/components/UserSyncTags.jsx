import React, { useState, useEffect } from 'react';
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { X, Plus, LoaderCircle } from "lucide-react";
import { Alert, AlertDescription } from "@/components/ui/alert";

const UserSyncTags = () => {
    const [availableTags, setAvailableTags] = useState([]);
    const [selectedTags, setSelectedTags] = useState([]);
    const [selectedTag, setSelectedTag] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [pendingTags, setPendingTags] = useState(new Set()); // Track tags being added/removed

    const fetchSelectedTags = async () => {
        try {
            const response = await fetch('/cp/bento/sync-tags', {
                headers: {
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken()
                },
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('Failed to fetch selected tags');
            }

            const tags = await response.json();
            setSelectedTags(tags);
        } catch (err) {
            console.error('Error fetching selected tags:', err);
        }
    };

    const fetchAvailableTags = async () => {
        try {
            const response = await fetch('/cp/bento/tags', {
                headers: {
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken()
                },
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('Failed to fetch tags');
            }

            const result = await response.json();
            const transformedTags = result.data.map(tag => ({
                id: tag.id,
                name: tag.attributes.name
            }));

            setAvailableTags(transformedTags);
            setLoading(false);
        } catch (err) {
            console.error('Error fetching tags:', err);
            setError('Failed to load tags');
            setLoading(false);
        }
    };

    useEffect(() => {
        const initializeData = async () => {
            await fetchAvailableTags();
            await fetchSelectedTags();
        };
        initializeData();
    }, []);

    const getCsrfToken = () => {
        const token = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        return token ? decodeURIComponent(token) : null;
    };

    const addTag = async (e) => {
        e.preventDefault();
        if (!selectedTag || selectedTags.includes(selectedTag) || pendingTags.has(selectedTag)) return;

        // Optimistic update
        setPendingTags(prev => new Set([...prev, selectedTag]));
        setSelectedTags(prev => [...prev, selectedTag]);
        setSelectedTag('');

        try {
            const response = await fetch('/cp/bento/sync-tags', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken()
                },
                credentials: 'include',
                body: JSON.stringify({ tag: selectedTag })
            });

            if (!response.ok) {
                throw new Error('Failed to add tag');
            }

            window.Statamic.$toast.success('Tag added successfully');
        } catch (err) {
            // Revert optimistic update
            setSelectedTags(prev => prev.filter(tag => tag !== selectedTag));
            window.Statamic.$toast.error('Failed to add tag');
        } finally {
            setPendingTags(prev => {
                const updated = new Set(prev);
                updated.delete(selectedTag);
                return updated;
            });
        }
    };

    const removeTag = async (tagToRemove) => {
        if (pendingTags.has(tagToRemove)) return;

        // Optimistic update
        setPendingTags(prev => new Set([...prev, tagToRemove]));
        setSelectedTags(prev => prev.filter(tag => tag !== tagToRemove));

        try {
            const response = await fetch('/cp/bento/sync-tags', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken()
                },
                credentials: 'include',
                body: JSON.stringify({ tag: tagToRemove })
            });

            if (!response.ok) {
                throw new Error('Failed to remove tag');
            }

            window.Statamic.$toast.success('Tag removed successfully');
        } catch (err) {
            // Revert optimistic update
            setSelectedTags(prev => [...prev, tagToRemove]);
            window.Statamic.$toast.error('Failed to remove tag');
        } finally {
            setPendingTags(prev => {
                const updated = new Set(prev);
                updated.delete(tagToRemove);
                return updated;
            });
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center p-4">
                <LoaderCircle className="w-6 h-6 animate-spin text-primary" />
            </div>
        );
    }

    if (error) {
        return (
            <Alert variant="destructive" className="mt-4">
                <AlertDescription>{error}</AlertDescription>
            </Alert>
        );
    }

    const availableTagsForSelect = availableTags.filter(tag =>
        !selectedTags.includes(tag.name)
    );

    return (
        <div className="mt-4 border-t pt-4">
            <p className="text-gray-700 dark:text-gray-600 mb-4">
                Select default tags to apply to new users when they are synchronized with Bento.
            </p>

            <div className="flex gap-2 items-start">
                <div className="flex-1">
                    <Select
                        value={selectedTag}
                        onValueChange={setSelectedTag}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Select a tag" />
                        </SelectTrigger>
                        <SelectContent>
                            {availableTagsForSelect.map((tag) => (
                                <SelectItem key={tag.id} value={tag.name}>
                                    {tag.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <Button
                    onClick={addTag}
                    disabled={!selectedTag || pendingTags.has(selectedTag)}
                    size="sm"
                    className="flex items-center gap-1"
                >
                    <Plus className="w-4 h-4" />
                    Add Tag
                </Button>
            </div>

            <div className="flex flex-wrap gap-2 mt-4">
                {selectedTags.map((tag) => (
                    <Badge
                        key={tag}
                        variant="secondary"
                        className={`flex items-center gap-1 ${pendingTags.has(tag) ? 'opacity-50' : ''}`}
                    >
                        {tag}
                        <button
                            onClick={() => removeTag(tag)}
                            className="ml-1 hover:bg-destructive hover:text-destructive-foreground rounded-full"
                            disabled={pendingTags.has(tag)}
                        >
                            <X className="w-3 h-3" />
                        </button>
                    </Badge>
                ))}
            </div>
        </div>
    );
};

export default UserSyncTags;
