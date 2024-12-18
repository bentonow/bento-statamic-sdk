import React, { useState, useEffect } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { ChevronDown, X } from "lucide-react";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { cn } from "@/lib/utils";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
} from "@/components/ui/dialog";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";

const BentoEvents = () => {
    const [events, setEvents] = useState([]);
    const [newEvent, setNewEvent] = useState({ name: '' });
    const [isOpen, setIsOpen] = useState(false);
    const [isCollapsibleOpen, setIsCollapsibleOpen] = useState(false);

    useEffect(() => {
        fetchEvents();
    }, []);

    const fetchEvents = async () => {
        try {
            const response = await fetch('/cp/bento/events');
            const data = await response.json();
            setEvents(data);
        } catch (error) {
            console.error('Error fetching events:', error);
        }
    };

    const handleDelete = async (eventId) => {
        try {
            let csrfToken = document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            if (csrfToken) {
                csrfToken = decodeURIComponent(csrfToken);
            }

            const response = await fetch(`/cp/bento/events/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-XSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'include',
            });

            if (response.ok) {
                setEvents(events.filter(event => event.id !== eventId));
                window.Statamic.$toast.success('Event deleted successfully');
            } else {
                const errorData = await response.json();
                console.error('Server error:', errorData);
                window.Statamic.$toast.error('Failed to delete event');
            }
        } catch (error) {
            console.error('Error deleting event:', error);
            window.Statamic.$toast.error('Failed to delete event');
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            let csrfToken = document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            if (csrfToken) {
                csrfToken = decodeURIComponent(csrfToken);
            }

            const response = await fetch('/cp/bento/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(newEvent),
            });

            if (response.ok) {
                setIsOpen(false);
                setNewEvent({ name: '' });
                fetchEvents();

                if (events.length > 3) {
                    setIsCollapsibleOpen(true);
                }
            } else {
                const errorData = await response.json();
                console.error('Server error:', errorData);
            }
        } catch (error) {
            console.error('Error creating event:', error);
        }
    };

    const visibleEvents = events.slice(0, 4);
    const collapsibleEvents = events.slice(4);
    const hasCollapsibleContent = events.length > 4;

    const EventRow = ({ event }) => (
        <TableRow key={event.id}>
            <TableCell>{event.name}</TableCell>
            <TableCell className="w-[50px]">
                <AlertDialog>
                    <AlertDialogTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 p-0 hover:bg-destructive hover:text-destructive-foreground"
                        >
                            <X className="h-4 w-4" />
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Delete Event</AlertDialogTitle>
                            <AlertDialogDescription>
                                Are you sure you want to delete this event? This action cannot be undone.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction
                                onClick={() => handleDelete(event.id)}
                                className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                            >
                                Delete
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </TableCell>
        </TableRow>
    );

    return (
        <div className="card p-4 mt-4">
            <div className="flex justify-between items-center mb-4">
                <h3 className="font-bold text-lg">Bento Events</h3>
                <Dialog open={isOpen} onOpenChange={setIsOpen}>
                    <DialogTrigger asChild>
                        <Button>Add Event</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create New Event</DialogTitle>
                            <DialogDescription>
                                Enter a name for your new event. This will be used to track events in your Bento account.
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="text-sm font-medium">Event Name</label>
                                <Input
                                    value={newEvent.name}
                                    onChange={(e) => setNewEvent({ name: e.target.value })}
                                    placeholder="Enter event name"
                                    className="mt-1"
                                    required
                                />
                            </div>
                            <Button type="submit" className="w-full">Create Event</Button>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead className="w-[50px]"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {visibleEvents.map((event) => (
                        <EventRow key={event.id} event={event} />
                    ))}
                </TableBody>
            </Table>

            {hasCollapsibleContent && (
                <Collapsible
                    open={isCollapsibleOpen}
                    onOpenChange={setIsCollapsibleOpen}
                >
                    <CollapsibleContent>
                        <Table>
                            <TableBody>
                                {collapsibleEvents.map((event) => (
                                    <EventRow key={event.id} event={event} />
                                ))}
                            </TableBody>
                        </Table>
                    </CollapsibleContent>

                    <CollapsibleTrigger className="flex justify-center w-full mt-2">
                        <div className="hover:bg-muted p-2 flex items-center gap-2 text-muted-foreground">
                            <ChevronDown
                                className={cn(
                                    "h-4 w-4 transition-transform duration-200",
                                    isCollapsibleOpen && "rotate-180"
                                )}
                            />
                            <span className="text-xs">
                                {isCollapsibleOpen ? "Show Less" : "Show More"}
                            </span>
                        </div>
                    </CollapsibleTrigger>
                </Collapsible>
            )}
        </div>
    );
};

export default BentoEvents;
